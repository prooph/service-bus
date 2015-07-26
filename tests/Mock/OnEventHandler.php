<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:52
 */

namespace Prooph\ServiceBusTest\Mock;

/**
 * Class OnEventHandler
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class OnEventHandler 
{
    /**
     * @var SomethingDone
     */
    private $lastEvent;

    private $eventCount = 0;

    /**
     * @param SomethingDone $event
     */
    public function onSomethingDone(SomethingDone $event)
    {
        $this->eventCount++;
        $this->lastEvent = $event;
    }

    /**
     * @return SomethingDone
     */
    public function lastEvent()
    {
        return $this->lastEvent;
    }

    public function eventCount()
    {
        return $this->eventCount;
    }
}
 