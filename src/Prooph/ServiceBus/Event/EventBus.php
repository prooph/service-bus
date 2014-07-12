<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:59
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Message\MessageDispatcherInterface;
use Prooph\ServiceBus\Message\MessageFactory;
use Prooph\ServiceBus\Message\MessageFactoryInterface;
use Prooph\ServiceBus\Message\Queue;
use Prooph\ServiceBus\Message\QueueInterface;
use Prooph\ServiceBus\Message\MessageNameProvider;
use Prooph\ServiceBus\Message\ServiceBusSerializable;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\MessageFactoryLoader;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class EventBus
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventBus implements EventBusInterface
{
    /**
     * @var MessageDispatcherInterface
     */
    protected $messageDispatcher;

    /**
     * @var QueueInterface
     */
    protected $queue;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var MessageFactoryLoader
     */
    protected $messageFactoryLoader;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @param string                     $aName
     * @param MessageDispatcherInterface $aMessageDispatcher
     */
    public function __construct($aName, MessageDispatcherInterface $aMessageDispatcher)
    {
        \Assert\that($aName)->notEmpty('EventBus.name must not be empty')->string('EventBus.name must be a string');

        $this->name              = $aName;
        $this->messageDispatcher = $aMessageDispatcher;
        $this->queue             = new Queue($this->name);
    }

    /**
     * @param mixed $anEvent
     *
     * @return void
     */
    public function publish($anEvent)
    {
        $results = $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('event' => $anEvent));

        if ($results->stopped()) {
            return;
        }

        $eventName = ($anEvent instanceof MessageNameProvider)? $anEvent->getMessageName() : get_class($anEvent);

        $message = $this->getMessageFactoryLoader()
            ->getMessageFactoryFor($eventName)
            ->fromEvent($anEvent, $this->name);

        $this->messageDispatcher->dispatch($this->queue, $message);

        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('event' => $anEvent, 'message' => $message));
    }

    /**
     * @param MessageFactoryLoader $aMessageFactory
     */
    public function setMessageFactoryLoader(MessageFactoryLoader $aMessageFactory)
    {
        $this->messageFactoryLoader = $aMessageFactory;
    }

    /**
     * @return MessageFactoryLoader
     */
    public function getMessageFactoryLoader()
    {
        return $this->messageFactoryLoader;
    }

    /**
     * @return EventManagerInterface
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->events = new EventManager(array(
                Definition::SERVICE_BUS_COMPONENT,
                'event_bus',
                __CLASS__
            ));
        }

        return $this->events;
    }
}
