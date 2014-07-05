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

use Prooph\ServiceBus\InvokeStrategy\InvokeStrategyInterface;

/**
 * Class SomethingDoneInvokeStrategy
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class SomethingDoneInvokeStrategy implements InvokeStrategyInterface
{
    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        return $aHandler instanceof SomethingDoneHandler && $aCommandOrEvent instanceof SomethingDone;
    }

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent)
    {
        $aHandler->somethingDone($aCommandOrEvent);
    }
}
 