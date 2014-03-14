<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 11:40
 */

namespace Codeliner\ServiceBus\Command;

use Codeliner\ServiceBus\Message\MessageDispatcherInterface;
use Codeliner\ServiceBus\Message\MessageFactory;
use Codeliner\ServiceBus\Message\MessageFactoryInterface;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\QueueInterface;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBus\Service\Definition;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class CommandBus
 *
 * @package Codeliner\ServiceBus\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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