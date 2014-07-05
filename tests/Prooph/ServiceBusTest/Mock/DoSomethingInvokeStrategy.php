<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 20:53
 */

namespace Prooph\ServiceBusTest\Mock;

use Prooph\ServiceBus\InvokeStrategy\InvokeStrategyInterface;

/**
 * Class DoSomethingInvokeStrategy
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DoSomethingInvokeStrategy implements InvokeStrategyInterface
{
    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        return $aHandler instanceof DoSomethingHandler && $aCommandOrEvent instanceof DoSomething;
    }

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent)
    {
        $aHandler->doSomething($aCommandOrEvent);
    }
}
 