<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:27
 */

namespace Prooph\ServiceBus\Message;

/**
 * Interface MessageDispatcherInterface
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface MessageDispatcherInterface
{
    /**
     * @param QueueInterface   $aQueue
     * @param MessageInterface $aMessage
     * @return void
     */
    public function dispatch(QueueInterface $aQueue, MessageInterface $aMessage);
}