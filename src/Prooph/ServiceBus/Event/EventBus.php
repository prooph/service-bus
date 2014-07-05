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
use Prooph\ServiceBus\Message\QueueInterface;
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
     * @var QueueInterface[]
     */
    protected $queueCollection;

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
     * @param QueueInterface[]           $aQueueCollection
     */
    public function __construct($aName, MessageDispatcherInterface $aMessageDispatcher, array $aQueueCollection)
    {
        \Assert\that($aName)->notEmpty('EventBus.name must not be empty')->string('EventBus.name must be a string');
        \Assert\that($aQueueCollection)->all()->isInstanceOf('Prooph\ServiceBus\Message\QueueInterface');

        $this->name              = $aName;
        $this->messageDispatcher = $aMessageDispatcher;
        $this->queueCollection   = $aQueueCollection;
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

        $message = $this->getMessageFactoryLoader()->get(get_class($anEvent))->fromEvent($anEvent, $this->name);

        foreach ($this->queueCollection as $queue) {

            $params = compact('message', 'queue');

            $results = $this->events()->trigger('publish_on_queue.pre', $this, $params);

            if ($results->stopped()) {
                continue;
            }

            $this->messageDispatcher->dispatch($queue, $message);

            $this->events()->trigger('publish_on_queue.post', $this, $params);
        }

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
