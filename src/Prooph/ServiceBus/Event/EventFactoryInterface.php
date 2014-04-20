<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:33
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Message\MessageInterface;

/**
 * Interface EventFactoryInterface
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface EventFactoryInterface
{
    /**
     * @param MessageInterface $aMessage
     * @return EventInterface
     */
    public function fromMessage(MessageInterface $aMessage);
}
