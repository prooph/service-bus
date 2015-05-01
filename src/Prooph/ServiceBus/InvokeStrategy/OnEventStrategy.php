<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:40
 */

namespace Prooph\ServiceBus\InvokeStrategy;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\Common\Messaging\HasMessageName;

/**
 * Class OnEventStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <contact@prooph.de>
 */
class OnEventStrategy extends AbstractInvokeStrategy
{
    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        if (! $aCommandOrEvent instanceof DomainEvent) {
            return false;
        }

        $handleMethod = 'on' . $this->determineEventName($aCommandOrEvent);

        return method_exists($aHandler, $handleMethod);
    }

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent)
    {
        $handleMethod = 'on' . $this->determineEventName($aCommandOrEvent);

        $aHandler->{$handleMethod}($aCommandOrEvent);
    }

    /**
     * @param mixed $anEvent
     * @return string
     */
    protected function determineEventName($anEvent)
    {
        $eventName = ($anEvent instanceof HasMessageName)? $anEvent->messageName() : get_class($anEvent);
        return join('', array_slice(explode('\\', $eventName), -1));
    }
}
 