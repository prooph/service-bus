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
use Prooph\ServiceBus\Service\CommandReceiverManager;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\EventReceiverManager;
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
     * @var CommandReceiverManager[]
     */
    protected $commandReceiverManagerQueueMap = array();

    /**
     * @var EventReceiverManager[]
     */
    protected $eventReceiverManagerQueueMap   = array();

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @param QueueInterface $aQueue
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException If no ReceiverManager is registered for Queue
     * @throws \Exception If handling of message fails
     * @return void
     */
    public function dispatch(QueueInterface $aQueue, MessageInterface $aMessage)
    {
        $results = $this->events()->trigger(
            __FUNCTION__. '.pre',
            $this,
            array('queue' => $aQueue, 'message' => $aMessage)
        );

        if ($results->stopped()) {
            return;
        }

        if ($aMessage->header()->type() === MessageHeader::TYPE_COMMAND) {
            if (!isset($this->commandReceiverManagerQueueMap[$aQueue->name()])) {
                throw new RuntimeException(
                    sprintf(
                        'No CommandReceiverManager registered for queue -%s-',
                        $aQueue->name()
                    )
                );
            }

            $commandReceiver = $this->commandReceiverManagerQueueMap[$aQueue->name()]
                ->get($aMessage->header()->sender());

            $commandReceiver->handle($aMessage);

            $this->events()->trigger(
                __FUNCTION__. '.post',
                $this,
                array('queue' => $aQueue, 'message' => $aMessage, 'receiver' => $commandReceiver)
            );

            return;
        }

        if ($aMessage->header()->type() === MessageHeader::TYPE_EVENT) {
            if (!isset($this->eventReceiverManagerQueueMap[$aQueue->name()])) {
                throw new RuntimeException(
                    sprintf(
                        'No EventReceiverManager registered for queue -%s-',
                        $aQueue->name()
                    )
                );
            }

            $eventReceiver = $this->eventReceiverManagerQueueMap[$aQueue->name()]
                ->get($aMessage->header()->sender());

            $eventReceiver->handle($aMessage);

            $this->events()->trigger(
                __FUNCTION__. '.post',
                $this,
                array('queue' => $aQueue, 'message' => $aMessage, 'receiver' => $eventReceiver)
            );

            return;
        }
    }

    /**
     * @param QueueInterface         $aQueue
     * @param CommandReceiverManager $aCommandReceiverManager
     */
    public function registerCommandReceiverManagerForQueue(
        QueueInterface $aQueue,
        CommandReceiverManager $aCommandReceiverManager
    ) {
        $this->commandReceiverManagerQueueMap[$aQueue->name()] = $aCommandReceiverManager;
    }

    /**
     * @param QueueInterface       $aQueue
     * @param EventReceiverManager $anEventReceiverManager
     */
    public function registerEventReceiverManagerForQueue(
        QueueInterface $aQueue,
        EventReceiverManager $anEventReceiverManager
    ) {
        $this->eventReceiverManagerQueueMap[$aQueue->name()] = $anEventReceiverManager;
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
