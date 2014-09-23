<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:27
 */

namespace Prooph\ServiceBusTest\Mock;

use Prooph\ServiceBus\Event;

/**
 * Class SomethingDoneListener
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class SomethingDoneListener
{
    /**
     * @var SomethingDone
     */
    private $lastEvent;

    /**
     * @param Event $event
     */
    public function somethingDone(Event $event)
    {
        $this->lastEvent = $event;
    }

    /**
     * @return Event
     */
    public function lastEvent()
    {
        return $this->lastEvent;
    }
}
 