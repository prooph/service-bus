<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:40
 */

namespace Codeliner\ServiceBus\InvokeStrategy;

use Codeliner\ServiceBus\Command\CommandInterface;
use Codeliner\ServiceBus\Event\EventInterface;

/**
 * Class OnEventStrategy
 *
 * @package Codeliner\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class OnEventStrategy implements InvokeStrategyInterface
{
    /**
     * @param mixed                           $aHandler
     * @param CommandInterface|EventInterface $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        if (! $aCommandOrEvent instanceof EventInterface) {
            return false;
        }

        $handleMethod = 'on' . $this->determineEventName($aCommandOrEvent);

        return method_exists($aHandler, $handleMethod);
    }

    /**
     * @param mixed                           $aHandler
     * @param CommandInterface|EventInterface $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent)
    {
        $handleMethod = 'on' . $this->determineEventName($aCommandOrEvent);

        $aHandler->{$handleMethod}($aCommandOrEvent);
    }

    /**
     * @param EventInterface $anEvent
     * @return string
     */
    protected function determineEventName(EventInterface $anEvent)
    {
        return join('', array_slice(explode('\\', get_class($anEvent)), -1));
    }
}
 