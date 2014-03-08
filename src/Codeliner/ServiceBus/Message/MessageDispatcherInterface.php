<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:27
 */

namespace Codeliner\ServiceBus\Message;

/**
 * Interface MessageDispatcherInterface
 *
 * @package Codeliner\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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