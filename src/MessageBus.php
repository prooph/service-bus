<?php

/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ListenerHandler;
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
    public const PRIORITY_PROMISE_REJECT = 1000;
    public const PRIORITY_INVOKE_HANDLER = 0;

    /**
     * @var ActionEventEmitter
     */
    protected $events;

    public function __construct(ActionEventEmitter $actionEventEmitter = null)
    {
        if (null === $actionEventEmitter) {
            $actionEventEmitter = new ProophActionEventEmitter([
                self::EVENT_DISPATCH,
                self::EVENT_FINALIZE,
            ]);
        }

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

    /**
     * @param mixed $message
     *
     * @return \React\Promise\Promise|void depends on the bus type
     */
    abstract public function dispatch($message);

    protected function triggerFinalize(ActionEvent $actionEvent): void
    {
        $actionEvent->setName(self::EVENT_FINALIZE);

        $this->events->dispatch($actionEvent);
    }

    /**
     * @param mixed $message
     */
    protected function getMessageName($message): string
    {
        if (\is_object($message)) {
            return \get_class($message);
        }

        if (\is_string($message)) {
            return $message;
        }

        return \gettype($message);
    }

    public function attach(string $eventName, callable $listener, int $priority = 0): ListenerHandler
    {
        return $this->events->attachListener($eventName, $listener, $priority);
    }

    public function detach(ListenerHandler $handler): void
    {
        $this->events->detachListener($handler);
    }
}
