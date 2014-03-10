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
     * @param QueueInterface $aQueue
     * @param MessageInterface $aMessage
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException If no CommandReceiverManager is registered for Queue
     * @return void
     */
    public function dispatch(QueueInterface $aQueue, MessageInterface $aMessage)
    {
        if (! isset($this->commandReceiverManagerQueueMap[$aQueue->name()])) {
            throw new RuntimeException(
                sprintf(
                    'No CommandReceiverManager registered for queue -%s-',
                    $aQueue->name()
                )
            );
        }

        $commandReceiver = $this->commandReceiverManagerQueueMap[$aQueue->name()]->get($aMessage->header()->sender());

        $commandReceiver->handle($aMessage);
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
}
