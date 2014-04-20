<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 22:44
 */

namespace Prooph\ServiceBus\InvokeStrategy;

use Prooph\ServiceBus\Command\CommandInterface;

/**
 * Class CallbackStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CallbackStrategy implements InvokeStrategyInterface
{

    /**
     * @param mixed                           $aHandler
     * @param CommandInterface|EventInterface $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        return is_callable($aHandler);
    }

    /**
     * @param mixed                           $aHandler
     * @param CommandInterface|EventInterface $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent)
    {
        call_user_func($aHandler, $aCommandOrEvent);
    }
}
