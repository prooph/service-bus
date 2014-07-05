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
use Prooph\ServiceBus\Service\CommandReceiverLoader;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\EventReceiverLoader;
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
     * @var CommandReceiverLoader[]
     */
    protected $commandReceiverLoaderQueueMap = array();

    /**
     * @var EventReceiverLoader[]
     */
    protected $eventReceiverLoaderQueueMap   = array();

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
            if (!isset($this->commandReceiverLoaderQueueMap[$aQueue->name()])) {
                throw new RuntimeException(
                    sprintf(
                        'No CommandReceiverLoader registered for queue -%s-',
                        $aQueue->name()
                    )
                );
            }

            $commandReceiver = $this->commandReceiverLoaderQueueMap[$aQueue->name()]
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
            if (!isset($this->eventReceiverLoaderQueueMap[$aQueue->name()])) {
                throw new RuntimeException(
                    sprintf(
                        'No EventReceiverLoader registered for queue -%s-',
                        $aQueue->name()
                    )
                );
            }

            $eventReceiver = $this->eventReceiverLoaderQueueMap[$aQueue->name()]
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
     * @param CommandReceiverLoader $aCommandReceiverLoader
     */
    public function registerCommandReceiverLoaderForQueue(
        QueueInterface $aQueue,
        CommandReceiverLoader $aCommandReceiverLoader
    ) {
        $this->commandReceiverLoaderQueueMap[$aQueue->name()] = $aCommandReceiverLoader;
    }

    /**
     * @param QueueInterface       $aQueue
     * @param EventReceiverLoader $anEventReceiverLoader
     */
    public function registerEventReceiverLoaderForQueue(
        QueueInterface $aQueue,
        EventReceiverLoader $anEventReceiverLoader
    ) {
        $this->eventReceiverLoaderQueueMap[$aQueue->name()] = $anEventReceiverLoader;
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
