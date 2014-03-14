<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.03.14 - 20:35
 */

namespace Codeliner\ServiceBus\Message;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Service\CommandReceiverManager;
use Codeliner\ServiceBus\Service\EventReceiverManager;

/**
 * Class InMemoryMessageDispatcher
 *
 * @package Codeliner\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
     * @param QueueInterface $aQueue
     * @param MessageInterface $aMessage
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException If no ReceiverManager is registered for Queue
     * @throws \Exception If handling of message fails
     * @return void
     */
    public function dispatch(QueueInterface $aQueue, MessageInterface $aMessage)
    {
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
}
