<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.03.14 - 20:35
 */

namespace Prooph\ServiceBus\Message;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class InMemoryMessageDispatcher
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class InMemoryMessageDispatcher implements MessageDispatcherInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EventBus
     */
    protected $eventBus;

    /**
     * @param CommandBus $commandBus
     * @param EventBus $eventBus
     */
    public function __construct(CommandBus $commandBus, EventBus $eventBus)
    {
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
    }

    /**
     * @param MessageInterface $message
     * @throws \Exception If handling of message fails
     * @return void
     */
    public function dispatch(MessageInterface $message)
    {
        $results = $this->events()->trigger(
            __FUNCTION__. '.pre',
            $this,
            array('message' => $message)
        );

        if ($results->stopped()) {
            return;
        }

        if ($message->header()->type() === MessageHeader::TYPE_COMMAND) {

            $this->commandBus->dispatch($message);


            $this->events()->trigger(
                __FUNCTION__. '.post',
                $this,
                array('message' => $message)
            );

            return;
        }

        if ($message->header()->type() === MessageHeader::TYPE_EVENT) {

            $this->eventBus->dispatch($message);


            $this->events()->trigger(
                __FUNCTION__. '.post',
                $this,
                array('message' => $message)
            );

            return;
        }
    }

    /**
     * @return EventManagerInterface
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->events = new EventManager(array(
                'message_dispatcher',
                __CLASS__
            ));
        }

        return $this->events;
    }
}
