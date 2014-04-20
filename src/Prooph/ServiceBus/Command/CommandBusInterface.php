<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 11:41
 */

namespace Prooph\ServiceBus\Command;

/**
 * Interface CommandBusInterface
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface CommandBusInterface
{
    /**
     * @param CommandInterface $aCommand
     *
     * @return void
     */
    public function send(CommandInterface $aCommand);
}