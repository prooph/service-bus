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
    private $messageDispatcher;

    /**
     * @var QueueInterface
     */
    private $queue;

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
        $message = $this->getMessageFactory()->fromCommand($aCommand, $this->name);

        $this->messageDispatcher->dispatch($this->queue, $message);
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