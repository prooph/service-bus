<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 22:44
 */

namespace Prooph\ServiceBus\InvokeStrategy;

/**
 * Class CallbackStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CallbackStrategy extends AbstractInvokeStrategy
{

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        return is_callable($aHandler);
    }

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent)
    {
        call_user_func($aHandler, $aCommandOrEvent);
    }
}
