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

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Service\Definition;
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

    protected $commandBus;

    protected $eventBus;

    /**
     * @param MessageInterface $message
     * @throws \Prooph\ServiceBus\Exception\RuntimeException If no ReceiverManager is registered for Queue
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

            //@TODO pass $message back to command bus


            $this->events()->trigger(
                __FUNCTION__. '.post',
                $this,
                array('message' => $message)
            );

            return;
        }

        if ($message->header()->type() === MessageHeader::TYPE_EVENT) {

            //@TODO pass $message back to event bus


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
                Definition::SERVICE_BUS_COMPONENT,
                'message_dispatcher',
                __CLASS__
            ));
        }

        return $this->events;
    }
}
