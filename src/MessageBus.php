<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 01/13/15 - 14:59
 */

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\Common\Messaging\HasMessageName;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;

/**
 * Class MessageBus
 *
 * Base class for a message bus implementation
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
abstract class MessageBus
{
    const EVENT_INITIALIZE          = "initialize";
    const EVENT_DETECT_MESSAGE_NAME = "detect-message-name";
    const EVENT_ROUTE               = "route";
    const EVENT_LOCATE_HANDLER      = "locate-handler";
    const EVENT_INVOKE_HANDLER      = "invoke-handler";
    const EVENT_HANDLE_ERROR        = "handle-error";
    const EVENT_FINALIZE            = "finalize";

    const EVENT_PARAM_MESSAGE         = 'message';
    const EVENT_PARAM_MESSAGE_NAME    = 'message-name';
    const EVENT_PARAM_MESSAGE_HANDLER = 'message-handler';
    const EVENT_PARAM_EXCEPTION       = 'exception';

    /**
     * @var ActionEventEmitter
     */
    protected $events;

    /**
     * @param mixed $message
     * @return mixed|void depends on the bus type
     */
    abstract public function dispatch($message);

    /**
     * @param ActionEventListenerAggregate $plugin
     */
    public function utilize(ActionEventListenerAggregate $plugin)
    {
        $plugin->attach($this->getActionEventEmitter());
    }

    /**
     * @param ActionEventListenerAggregate $plugin
     */
    public function deactivate(ActionEventListenerAggregate $plugin)
    {
        $plugin->detach($this->getActionEventEmitter());
    }

    /**
     * @param mixed $message
     * @param ActionEvent $actionEvent
     */
    protected function initialize($message, ActionEvent $actionEvent)
    {
        $actionEvent->setParam(self::EVENT_PARAM_MESSAGE, $message);

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

    /**
     * @param ActionEvent $actionEvent
     * @param \Exception $ex
     * @throws Exception\MessageDispatchException
     */
    protected function handleException(ActionEvent $actionEvent, \Exception $ex)
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

    /**
     * @param ActionEvent $actionEvent
     * @throws Exception\RuntimeException
     */
    protected function trigger(ActionEvent $actionEvent)
    {
        $this->getActionEventEmitter()->dispatch($actionEvent);

        if ($actionEvent->propagationIsStopped()) {
            throw new RuntimeException("Dispatch has stopped unexpectedly.");
        }
    }

    /**
     * @param ActionEvent $actionEvent
     */
    protected function triggerError(ActionEvent $actionEvent)
    {
        $actionEvent->setName(self::EVENT_HANDLE_ERROR);

        $this->getActionEventEmitter()->dispatch($actionEvent);
    }

    /**
     * @param ActionEvent $actionEvent
     */
    protected function triggerFinalize(ActionEvent $actionEvent)
    {
        $actionEvent->setName(self::EVENT_FINALIZE);

        $this->getActionEventEmitter()->dispatch($actionEvent);
    }


    /**
     * Inject an ActionEventDispatcher instance
     *
     * @param  ActionEventEmitter $actionEventDispatcher
     * @return void
     */
    public function setActionEventEmitter(ActionEventEmitter $actionEventDispatcher)
    {
        $this->events = $actionEventDispatcher;
    }

    /**
     * Retrieve the action event dispatcher
     *
     * Lazy-loads a dispatcher if none is registered.
     *
     * @return ActionEventEmitter
     */
    public function getActionEventEmitter()
    {
        if (null === $this->events) {
            $this->setActionEventEmitter(new ProophActionEventEmitter());
        }

        return $this->events;
    }

    /**
     * @param mixed $message
     * @return string
     */
    protected function getMessageName($message)
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
