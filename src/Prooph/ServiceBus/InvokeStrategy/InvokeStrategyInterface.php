<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 22:38
 */

namespace Prooph\ServiceBus\InvokeStrategy;
use Prooph\ServiceBus\Command\CommandInterface;
use Prooph\ServiceBus\Event\EventInterface;

/**
 * Interface CommandHandlerInvokeStrategyInterface
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface InvokeStrategyInterface
{
    /**
     * @param mixed                           $aHandler
     * @param CommandInterface|EventInterface $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent);

    /**
     * @param mixed                           $aHandler
     * @param CommandInterface|EventInterface $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent);
}
 