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
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\Common\Messaging\HasMessageName;
use Prooph\ServiceBus\Exception\MessageDispatchException;

/**
 * Base class for a message bus implementation
 */
abstract class MessageBus
{
    public const EVENT_DISPATCH = 'dispatch';
    public const EVENT_FINALIZE = 'finalize';

    public const EVENT_PARAM_MESSAGE = 'message';
    public const EVENT_PARAM_MESSAGE_NAME = 'message-name';
    public const EVENT_PARAM_MESSAGE_HANDLER = 'message-handler';
    public const EVENT_PARAM_EXCEPTION = 'exception';
    public const EVENT_PARAM_MESSAGE_HANDLED = 'message-handled';

    public const PRIORITY_INITIALIZE = 400000;
    public const PRIORITY_DETECT_MESSAGE_NAME = 300000;
    public const PRIORITY_ROUTE = 200000;
    public const PRIORITY_LOCATE_HANDLER = 100000;
    public const PRIORITY_INVOKE_HANDLER = 0;

    /**
     * @var ActionEventEmitter
     */
    protected $events;

    /**
     * @param mixed $message
     *
     * @return \React\Promise\Promise|void depends on the bus type
     */
    abstract public function dispatch($message);

    public function utilize(ActionEventListenerAggregate $plugin): void
    {
        $plugin->attach($this->getActionEventEmitter());
    }

    public function deactivate(ActionEventListenerAggregate $plugin): void
    {
        $plugin->detach($this->getActionEventEmitter());
    }

    protected function triggerFinalize(ActionEvent $actionEvent): void
    {
        $actionEvent->setName(self::EVENT_FINALIZE);

        $this->getActionEventEmitter()->dispatch($actionEvent);
    }

    public function setActionEventEmitter(ActionEventEmitter $actionEventEmitter): void
    {
        $actionEventEmitter->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, false);
                $message = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);

                if ($message instanceof HasMessageName) {
                    $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_NAME, $message->messageName());
                }
            },
            self::PRIORITY_INITIALIZE
        );

        $actionEventEmitter->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_NAME) === null) {
                    $actionEvent->setParam(
                        self::EVENT_PARAM_MESSAGE_NAME,
                        $this->getMessageName($actionEvent->getParam(self::EVENT_PARAM_MESSAGE))
                    );
                }
            },
            self::PRIORITY_DETECT_MESSAGE_NAME
        );

        $actionEventEmitter->attachListener(
            self::EVENT_FINALIZE,
            function (ActionEvent $actionEvent): void {
                if ($exception = $actionEvent->getParam(self::EVENT_PARAM_EXCEPTION)) {
                    throw MessageDispatchException::failed($exception);
                }
            }
        );

        $this->events = $actionEventEmitter;
    }

    public function getActionEventEmitter(): ActionEventEmitter
    {
        if (null === $this->events) {
            $reflection = new \ReflectionClass($this);
            $availableEventNames = array_values(array_filter(
                $reflection->getConstants(),
                function (string $key): bool {
                    return (bool) ! substr_compare($key, 'EVENT_', 0, 6, false);
                },
                ARRAY_FILTER_USE_KEY
            ));
            $this->setActionEventEmitter(new ProophActionEventEmitter($availableEventNames));
        }

        return $this->events;
    }

    /**
     * @param mixed $message
     *
     * @return string
     */
    protected function getMessageName($message): string
    {
        if (is_object($message)) {
            return get_class($message);
        }

        if (is_string($message)) {
            return $message;
        }

        return gettype($message);
    }
}
