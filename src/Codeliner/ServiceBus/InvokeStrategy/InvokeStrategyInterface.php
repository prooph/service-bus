<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 22:38
 */

namespace Codeliner\ServiceBus\InvokeStrategy;
use Codeliner\ServiceBus\Command\CommandInterface;

/**
 * Interface CommandHandlerInvokeStrategyInterface
 *
 * @package Codeliner\ServiceBus\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface InvokeStrategyInterface
{
    /**
     * @param mixed            $aHandler
     * @param CommandInterface $aCommand
     * @return bool
     */
    public function canInvoke($aHandler, CommandInterface $aCommand);

    /**
     * @param mixed            $aHandler
     * @param CommandInterface $aCommand
     */
    public function invoke($aHandler, CommandInterface $aCommand);
}
 