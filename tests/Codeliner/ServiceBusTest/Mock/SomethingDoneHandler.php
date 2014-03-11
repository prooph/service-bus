<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:27
 */

namespace Codeliner\ServiceBusTest\Mock;

/**
 * Class SomethingDoneHandler
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SomethingDoneHandler
{
    /**
     * @var SomethingDone
     */
    private $lastEvent;

    /**
     * @param SomethingDone $event
     */
    public function somethingDone(SomethingDone $event)
    {
        $this->lastEvent = $event;
    }

    /**
     * @return SomethingDone
     */
    public function lastEvent()
    {
        return $this->lastEvent;
    }
}
 