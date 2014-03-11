<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:29
 */

namespace Codeliner\ServiceBus\Event;

use Codeliner\ServiceBus\Message\MessageInterface;

/**
 * Interface EventReceiverInterface
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface EventReceiverInterface
{
    /**
     * @param MessageInterface $aMessage
     * @return void
     */
    public function handle(MessageInterface $aMessage);
}
 