<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 22:51
 */

namespace Prooph\ServiceBus\InvokeStrategy;

/**
 * Class ForwardToMessageDispatcherStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ForwardToMessageDispatcherStrategy extends AbstractInvokeStrategy
{
    /**
     * @var
     */
    protected $messageFactory;

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    protected function canInvoke($aHandler, $aCommandOrEvent)
    {
        // TODO: Implement canInvoke() method.
    }

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     */
    protected function invoke($aHandler, $aCommandOrEvent)
    {
        // TODO: Implement invoke() method.
    }


}
 