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
use Prooph\ServiceBus\Exception\RuntimeException;

/**
 * Base class for a message bus implementation
 */
abstract class MessageBus
{
    public const EVENT_INITIALIZE          = "initialize";
    public const EVENT_DETECT_MESSAGE_NAME = "detect-message-name";
    public const EVENT_ROUTE               = "route";
    public const EVENT_LOCATE_HANDLER      = "locate-handler";
    public const EVENT_INVOKE_HANDLER      = "invoke-handler";
    public const EVENT_HANDLE_ERROR        = "handle-error";
    public const EVENT_FINALIZE            = "finalize";

    public const EVENT_PARAM_MESSAGE         = 'message';
    public const EVENT_PARAM_MESSAGE_NAME    = 'message-name';
    public const EVENT_PARAM_MESSAGE_HANDLER = 'message-handler';
    public const EVENT_PARAM_EXCEPTION       = 'exception';
    public const EVENT_PARAM_MESSAGE_HANDLED = 'message-handled';

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

    /**
     * @param mixed $message
     * @param ActionEvent $actionEvent
     */
    protected function initialize($message, ActionEvent $actionEvent): void
    {
        $actionEvent->setParam(self::EVENT_PARAM_MESSAGE, $message);
        $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, false);

        if ($message instanceof HasMessageName) {
            $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_NAME, $message->messageName());
        }

        $actionEvent->setName(self::EVENT_INITIALIZE);

        $this->trigger($actionEvent);

        if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_NAME) === null) {
            $actionEvent->setName(self::EVENT_DETECT_MESSAGE_NAME);

            $this->trigger($actionEvent);

            if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_NAME) === null) {
                $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_NAME, $this->getMessageName($message));
            }
        }
    }

    protected function handleException(ActionEvent $actionEvent, \Throwable $ex): void
    {
        $failedPhase = $actionEvent->getName();

        $actionEvent->setParam(self::EVENT_PARAM_EXCEPTION, $ex);
        $this->triggerError($actionEvent);
        $this->triggerFinalize($actionEvent);

        //Check if a listener has removed the exception to indicate that it was able to handle it
        if ($ex = $actionEvent->getParam(self::EVENT_PARAM_EXCEPTION)) {
            $actionEvent->setName($failedPhase);
            throw MessageDispatchException::failed($actionEvent, $ex);
        }
    }

    protected function trigger(ActionEvent $actionEvent): void
    {
        $this->getActionEventEmitter()->dispatch($actionEvent);

        if ($actionEvent->propagationIsStopped()) {
            throw new RuntimeException("Dispatch has stopped unexpectedly.");
        }
    }

    protected function triggerError(ActionEvent $actionEvent): void
    {
        $actionEvent->setName(self::EVENT_HANDLE_ERROR);

        $this->getActionEventEmitter()->dispatch($actionEvent);
    }

    protected function triggerFinalize(ActionEvent $actionEvent): void
    {
        $actionEvent->setName(self::EVENT_FINALIZE);

        $this->getActionEventEmitter()->dispatch($actionEvent);
    }

    public function setActionEventEmitter(ActionEventEmitter $actionEventDispatcher): void
    {
        $this->events = $actionEventDispatcher;
    }

    public function getActionEventEmitter(): ActionEventEmitter
    {
        if (null === $this->events) {
            $reflection = new \ReflectionClass($this);
            $availableEventNames = array_values(array_filter(
                $reflection->getConstants(),
                function (string $key) {
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
