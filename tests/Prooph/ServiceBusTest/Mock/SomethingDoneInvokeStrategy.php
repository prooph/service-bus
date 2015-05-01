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

use Prooph\Common\Messaging\DomainEvent;
use Prooph\ServiceBus\InvokeStrategy\AbstractInvokeStrategy;

/**
 * Class SomethingDoneInvokeStrategy
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class SomethingDoneInvokeStrategy extends AbstractInvokeStrategy
{
    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        return $aHandler instanceof SomethingDoneListener && $aCommandOrEvent instanceof DomainEvent;
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
 