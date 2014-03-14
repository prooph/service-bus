<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:59
 */

namespace Codeliner\ServiceBus\Event;

use Codeliner\ServiceBus\Message\MessageDispatcherInterface;
use Codeliner\ServiceBus\Message\MessageFactory;
use Codeliner\ServiceBus\Message\MessageFactoryInterface;
use Codeliner\ServiceBus\Message\QueueInterface;

/**
 * Class EventBus
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventBus implements EventBusInterface
{
    /**
     * @var MessageDispatcherInterface
     */
    private $messageDispatcher;

    /**
     * @var QueueInterface[]
     */
    private $queueCollection;

    /**
     * @var string
     */
    private $name;

    /**
     * @var MessageFactoryInterface
     */
    private $messageFactory;

    /**
     * @param string                     $aName
     * @param MessageDispatcherInterface $aMessageDispatcher
     * @param QueueInterface[]           $aQueueCollection
     */
    public function __construct($aName, MessageDispatcherInterface $aMessageDispatcher, array $aQueueCollection)
    {
        \Assert\that($aName)->notEmpty('EventBus.name must not be empty')->string('EventBus.name must be a string');
        \Assert\that($aQueueCollection)->all()->isInstanceOf('Codeliner\ServiceBus\Message\QueueInterface');

        $this->name              = $aName;
        $this->messageDispatcher = $aMessageDispatcher;
        $this->queueCollection   = $aQueueCollection;
    }

    /**
     * @param EventInterface $anEvent
     *
     * @return void
     */
    public function publish(EventInterface $anEvent)
    {
        $message = $this->getMessageFactory()->fromEvent($anEvent, $this->name);

        foreach ($this->queueCollection as $queue) {
            $this->messageDispatcher->dispatch($queue, $message);
        }
    }

    /**
     * @param MessageFactoryInterface $aMessageFactory
     */
    public function setMessageFactory(MessageFactoryInterface $aMessageFactory)
    {
        $this->messageFactory = $aMessageFactory;
    }

    /**
     * @return MessageFactoryInterface
     */
    public function getMessageFactory()
    {
        if (is_null($this->messageFactory)) {
            $this->messageFactory = new MessageFactory();
        }

        return $this->messageFactory;
    }
}
