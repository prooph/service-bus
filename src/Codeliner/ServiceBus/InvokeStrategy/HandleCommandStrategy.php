<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 21:41
 */

namespace Codeliner\ServiceBus\InvokeStrategy;

use Codeliner\ServiceBus\Command\CommandInterface;

/**
 * Class HandleCommandStrategy
 *
 * @package Codeliner\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class HandleCommandStrategy implements InvokeStrategyInterface
{

    /**
     * @param mixed            $aHandler
     * @param CommandInterface $aCommandOrEvent
     * @return bool
     */
    public function canInvoke($aHandler, $aCommandOrEvent)
    {
        if (! $aCommandOrEvent instanceof CommandInterface) {
            return false;
        }

        $handleMethod = 'handle' . $this->determineCommandName($aCommandOrEvent);

        return method_exists($aHandler, $handleMethod);
    }

    /**
     * @param mixed $aHandler
     * @param CommandInterface $aCommandOrEvent
     */
    public function invoke($aHandler, $aCommandOrEvent)
    {
        $handleMethod = 'handle' . $this->determineCommandName($aCommandOrEvent);

        $aHandler->{$handleMethod}($aCommandOrEvent);
    }

    /**
     * @param CommandInterface $aCommand
     * @return string
     */
    protected function determineCommandName(CommandInterface $aCommand)
    {
        return join('', array_slice(explode('\\', get_class($aCommand)), -1));
    }
}
 