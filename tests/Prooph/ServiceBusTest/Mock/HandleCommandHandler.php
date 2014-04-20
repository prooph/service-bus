<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 21:47
 */

namespace Prooph\ServiceBusTest\Mock;

/**
 * Class HandleCommandHandler
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class HandleCommandHandler
{
    /**
     * @var DoSomething
     */
    private $lastCommand;

    /**
     * @param DoSomething $aCommand
     */
    public function handleDoSomething(DoSomething $aCommand)
    {
        $this->lastCommand = $aCommand;
    }

    /**
     * @return DoSomething
     */
    public function lastCommand()
    {
        return $this->lastCommand;
    }
}
