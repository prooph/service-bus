<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 11:40
 */

namespace Prooph\ServiceBus\Command;

use Prooph\ServiceBus\Message\MessageDispatcherInterface;
use Prooph\ServiceBus\Message\MessageFactory;
use Prooph\ServiceBus\Message\MessageFactoryInterface;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\QueueInterface;
use Prooph\ServiceBus\Message\StandardMessage;
use Prooph\ServiceBus\Service\Definition;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class CommandBus
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandBus implements CommandBusInterface
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
     * @var MessageFactoryInterface
     */
    protected $messageFactory;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @param string                     $aName
     * @param MessageDispatcherInterface $aMessageDispatcher
     * @param QueueInterface             $aQueue
     */
    public function __construct($aName, MessageDispatcherInterface $aMessageDispatcher, QueueInterface $aQueue)
    {
        \Assert\that($aName)->notEmpty('CommandBus.name must not be empty')->string('CommandBus.name must be a string');

        $this->name              = $aName;
        $this->messageDispatcher = $aMessageDispatcher;
        $this->queue             = $aQueue;
    }

    /**
     * @param CommandInterface $aCommand
     *
     * @return void
     */
    public function send(CommandInterface $aCommand)
    {
        $results = $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('command' => $aCommand));

        if ($results->stopped()) {
            return;
        }

        $message = $this->getMessageFactory()->fromCommand($aCommand, $this->name);

        $this->messageDispatcher->dispatch($this->queue, $message);

        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('command' => $aCommand, 'message' => $message));
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

    /**
     * @return EventManagerInterface
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->events = new EventManager(array(
                Definition::SERVICE_BUS_COMPONENT,
                'command_bus',
                __CLASS__
            ));
        }

        return $this->events;
    }
}