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

use Codeliner\ServiceBus\Command\CommandInterface;
use Codeliner\ServiceBus\Event\EventInterface;
use Codeliner\ServiceBus\InvokeStrategy\InvokeStrategyInterface;

/**
 * Class SomethingDoneInvokeStrategy
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SomethingDoneInvokeStrategy implements InvokeStrategyInterface
{
    /**
     * @param mixed $aHandler
     * @param CommandInterface|EventInterface $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        return $aHandler instanceof SomethingDoneHandler && $aCommandOrEvent instanceof SomethingDone;
    }

    /**
     * @param mixed $aHandler
     * @param CommandInterface|EventInterface $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent)
    {
        $aHandler->somethingDone($aCommandOrEvent);
    }
}
 