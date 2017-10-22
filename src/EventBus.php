<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\ServiceBus\Exception\EventListenerException;

/**
 * An event bus is capable of dispatching a message to multiple listeners.
 */
class EventBus extends MessageBus
{
    public const EVENT_PARAM_EVENT_LISTENERS = 'event-listeners';

    /**
     * Flag that enables or disables behaviour to collect listener exceptions
     *
     * @var bool
     */
    protected $collectExceptions = false;

    /**
     * @var array
     */
    protected $collectedExceptions = [];

    public function __construct(ActionEventEmitter $actionEventEmitter = null)
    {
        parent::__construct($actionEventEmitter);

        $this->events->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $event = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                $handled = false;
                $caughtExceptions = [];

                foreach (array_filter($actionEvent->getParam(self::EVENT_PARAM_EVENT_LISTENERS, []), 'is_callable') as $eventListener) {
                    try {
                        $eventListener($event);
                        $handled = true;
                    } catch (\Throwable $exception) {
                        if ($this->collectExceptions) {
                            $caughtExceptions[] = $exception;
                        } else {
                            throw $exception;
                        }
                    }
                }

                if ($handled) {
                    $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, true);
                }

                foreach ($caughtExceptions as $ex) {
                    $this->collectedExceptions[] = $ex;
                }
            },
            self::PRIORITY_INVOKE_HANDLER
        );

        $this->events->attachListener(
            self::EVENT_FINALIZE,
            function (ActionEvent $actionEvent): void {
                $target = $actionEvent->getTarget();

                if (empty($target->collectedExceptions)) {
                    return;
                }

                $exceptions = $target->collectedExceptions;
                $target->collectedExceptions = [];

                $actionEvent->setParam(MessageBus::EVENT_PARAM_EXCEPTION, EventListenerException::collected(...$exceptions));
            },
            1000
        );
    }

    /**
     * @param mixed $event
     */
    public function dispatch($event): void
    {
        $actionEventEmitter = $this->events;

        $actionEvent = $actionEventEmitter->getNewActionEvent(
            self::EVENT_DISPATCH,
            $this,
            [
                self::EVENT_PARAM_MESSAGE => $event,
            ]
        );

        try {
            $actionEventEmitter->dispatch($actionEvent);
        } catch (\Throwable $exception) {
            $actionEvent->setParam(self::EVENT_PARAM_EXCEPTION, $exception);
        } finally {
            $this->triggerFinalize($actionEvent);
        }
    }

    public function enableCollectExceptions(): void
    {
        $this->collectExceptions = true;
    }

    public function disableCollectExceptions(): void
    {
        $this->collectExceptions = false;
    }

    public function isCollectingException(): bool
    {
        return $this->collectExceptions;
    }

    public function addCollectedException(\Throwable $e): void
    {
        $this->collectedExceptions[] = $e;
    }
}
