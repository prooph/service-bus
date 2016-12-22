<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;

/**
 * An event bus is capable of dispatching a message to multiple listeners.
 */
class EventBus extends MessageBus
{
    public const EVENT_PARAM_EVENT_LISTENERS = 'event-listeners';

    public function __construct(ActionEventEmitter $actionEventEmitter = null)
    {
        parent::__construct($actionEventEmitter);

        $this->events->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $event = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                $handled = false;

                foreach (array_filter($actionEvent->getParam(self::EVENT_PARAM_EVENT_LISTENERS, []), 'is_callable') as $eventListener) {
                    $eventListener($event);
                    $handled = true;
                }

                if ($handled) {
                    $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, true);
                }
            },
            self::PRIORITY_INVOKE_HANDLER
        );
    }

    /**
     * @param mixed $event
     */
    public function dispatch($event): void
    {
        $actionEventEmitter = $this->getActionEventEmitter();

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
}
