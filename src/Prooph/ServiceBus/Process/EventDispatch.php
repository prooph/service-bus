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
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Message\MessageNameProvider;
use Zend\EventManager\Event as ZendEvent;
use Zend\Log\Logger;
use Zend\Log\LoggerInterface;

/**
 * Class EventDispatch
 *
 * @package Prooph\ServiceBus\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventDispatch extends ZendEvent
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
     * @param mixed $event
     * @param EventBus $eventBus
     * @throws \InvalidArgumentException
     * @return CommandDispatch
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
     * @return \ArrayObject(index => null|string|object)
     */
    public function getEventListeners()
    {
        return $this->getParam('event-listeners', new \ArrayObject());
    }

    /**
     * @param array(index => null|string|object) $eventHandlerCollection
     * @return EventDispatch
     * @throws \InvalidArgumentException
     */
    public function setEventListeners(array $eventHandlerCollection)
    {
        $this->setParam('event-listeners', new \ArrayObject());

        foreach ($eventHandlerCollection as $eventHandler) {
            $this->addEventHandler($eventHandler);
        }

        return $this;
    }

    /**
     * @param null|string|object $eventListener
     * @throws \InvalidArgumentException
     * @return EventDispatch
     */
    public function addEventHandler($eventListener)
    {
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
    }
}
 