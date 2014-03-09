<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 20:53
 */

namespace Codeliner\ServiceBusTest\Mock;

use Codeliner\ServiceBus\Command\CommandInterface;
use Codeliner\ServiceBus\InvokeStrategy\InvokeStrategyInterface;

/**
 * Class DoSomethingInvokeStrategy
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DoSomethingInvokeStrategy implements InvokeStrategyInterface
{
    /**
     * @param mixed $aHandler
     * @param CommandInterface $aCommand
     * @return bool
     */
    public function canInvoke($aHandler, CommandInterface $aCommand)
    {
        return $aHandler instanceof DoSomethingHandler && $aCommand instanceof DoSomething;
    }

    /**
     * @param mixed $aHandler
     * @param CommandInterface $aCommand
     */
    public function invoke($aHandler, CommandInterface $aCommand)
    {
        $aHandler->doSomething($aCommand);
    }
}
 