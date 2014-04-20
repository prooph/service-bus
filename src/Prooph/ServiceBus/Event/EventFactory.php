<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:03
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageInterface;

/**
 * Class EventFactory
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventFactory implements EventFactoryInterface
{
    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return EventInterface
     */
    public function fromMessage(MessageInterface $aMessage)
    {
        $eventClass = $aMessage->name();

        if (!class_exists($eventClass)) {
            throw new RuntimeException(
                sprintf(
                    "Class for %s event can not be found",
                    $eventClass
                )
            );
        }

        return new $eventClass(
            $aMessage->payload(),
            $aMessage->header()->version(),
            $aMessage->header()->uuid(),
            $aMessage->header()->createdOn()
        );
    }
}
