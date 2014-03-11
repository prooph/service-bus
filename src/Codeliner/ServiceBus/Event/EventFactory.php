<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:03
 */

namespace Codeliner\ServiceBus\Event;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Message\MessageInterface;

/**
 * Class EventFactory
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventFactory implements EventFactoryInterface
{
    /**
     * @param MessageInterface $aMessage
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException
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
