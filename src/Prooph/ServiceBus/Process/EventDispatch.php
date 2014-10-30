<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 21:30
 */

namespace Prooph\ServiceBus\Process;

use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Message\MessageNameProvider;
use Zend\EventManager\Event as ProcessEvent;
use Zend\Log\Logger;
use Zend\Log\LoggerInterface;

/**
 * Class EventDispatch
 *
 * @package Prooph\ServiceBus\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventDispatch extends ProcessEvent
{
    const INITIALIZE          = "initialize";
    const DETECT_MESSAGE_NAME = "detect-message-name";
    const ROUTE               = "route";
    const LOCATE_LISTENER     = "locate-listener";
    const INVOKE_LISTENER     = "invoke-listener";
    const HANDLE_ERROR        = "handle-error";
    const FINALIZE            = "finalize";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $isLoggingEnabled = false;

    /**
     * @param mixed $event
     * @param EventBus $eventBus
     * @throws \InvalidArgumentException
     * @return EventDispatch
     */
    public static function initializeWith($event, EventBus $eventBus)
    {
        $instance = new self(self::INITIALIZE, $eventBus, array('event' => $event));

        if ($event instanceof MessageNameProvider) {
            $instance->setEventName($event->getMessageName());
        }

        if ($event instanceof MessageInterface) {
            if ($event->header()->type() !== MessageHeader::TYPE_EVENT) {
                throw new \InvalidArgumentException(
                    sprintf("Message %s cannot be handled. Message is not of type event.", $event->name())
                );
            }

            $instance->setEventName($event->name());
        }

        return $instance;
    }

    /**
     * @return string|null
     */
    public function getEventName()
    {
        return $this->getParam('event-name');
    }

    /**
     * @param string $eventName
     * @return EventDispatch
     * @throws \InvalidArgumentException
     */
    public function setEventName($eventName)
    {
        \Assert\that($eventName)->notEmpty("Invalid event name provided.")->string("Invalid event name provided.");

        $this->setParam('event-name', $eventName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->getParam('event');
    }

    /**
     * @param mixed $event
     * @return EventDispatch
     */
    public function setEvent($event)
    {
        $this->setParam('event', $event);
        return $this;
    }

    /**
     * @return \ArrayObject(index => callable|string|object)
     */
    public function getEventListeners()
    {
        //We cannot work with a simple default here, cause we need the exact reference to the listeners stack
        $eventListeners = $this->getParam('event-listeners');

        if (is_null($eventListeners)) {
            $eventListeners = new \ArrayObject();

            $this->setParam('event-listeners', $eventListeners);
        }

        return $eventListeners;
    }

    /**
     * @param array(index => callable|string|object) $eventHandlerCollection
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return EventDispatch
     */
    public function setEventListeners(array $eventHandlerCollection)
    {
        if ($this->getName() === self::LOCATE_LISTENER || $this->getName() === self::INVOKE_LISTENER) {
            throw new RuntimeException(
                "Cannot set event listeners. EventDispatch is already in dispatching phase."
            );
        }

        $this->setParam('event-listeners', new \ArrayObject());

        foreach ($eventHandlerCollection as $eventHandler) {
            $this->addEventListener($eventHandler);
        }

        return $this;
    }

    /**
     * @param callable|string|object $eventListener
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @throws \InvalidArgumentException
     * @return EventDispatch
     */
    public function addEventListener($eventListener)
    {
        if ($this->getName() === self::LOCATE_LISTENER || $this->getName() === self::INVOKE_LISTENER) {
            throw new RuntimeException(
                "Cannot set event listeners. EventDispatch is already in dispatching phase."
            );
        }

        if (! is_string($eventListener) && ! is_object($eventListener) && ! is_callable($eventListener)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid event listener provided. Expected type is string, object or callable but type of %s given.",
                gettype($eventListener)
            ));
        }

        $this->getEventListeners()[] = $eventListener;

        return $this;
    }

    /**
     * @param callable|string|object $eventListener
     * @return EventDispatch
     * @throws \InvalidArgumentException
     */
    public function setCurrentEventListener($eventListener)
    {
        if (! is_string($eventListener) && ! is_object($eventListener) && ! is_callable($eventListener)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid event listener provided. Expected type is string, object or callable but type of %s given.",
                gettype($eventListener)
            ));
        }

        $this->setParam('current-event-listener', $eventListener);

        return $this;
    }

    /**
     * @return callable|string|object|null
     */
    public function getCurrentEventListener()
    {
        return $this->getParam('current-event-listener');
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            $this->logger = new Logger();
            $this->logger->addWriter('null');
        }

        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function useLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->isLoggingEnabled = true;
    }

    /**
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->isLoggingEnabled;
    }

    /**
     * @param \Exception $exception
     * @return $this
     */
    public function setException(\Exception $exception)
    {
        $this->setParam('exception', $exception);

        return $this;
    }

    /**
     * @return null|\Exception
     */
    public function getException()
    {
        return $this->getParam('exception');
    }
}
 