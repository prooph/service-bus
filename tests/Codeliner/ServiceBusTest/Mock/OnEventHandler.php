<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:52
 */

namespace Codeliner\ServiceBusTest\Mock;

use Codeliner\ServiceBus\Event\EventInterface;

/**
 * Class OnEventHandler
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
 