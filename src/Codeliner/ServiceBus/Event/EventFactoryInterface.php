<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:33
 */

namespace Codeliner\ServiceBus\Event;

use Codeliner\ServiceBus\Message\MessageInterface;

/**
 * Interface EventFactoryInterface
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface EventFactoryInterface
{
    /**
     * @param MessageInterface $aMessage
     * @return EventInterface
     */
    public function fromMessage(MessageInterface $aMessage);
}
