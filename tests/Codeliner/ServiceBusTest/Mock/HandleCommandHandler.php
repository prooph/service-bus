<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 21:47
 */

namespace Codeliner\ServiceBusTest\Mock;

/**
 * Class HandleCommandHandler
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
