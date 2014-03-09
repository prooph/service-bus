<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 20:51
 */

namespace Codeliner\ServiceBusTest\Mock;

/**
 * Class DoSomethingHandler
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DoSomethingHandler
{
    /**
     * @var DoSomething
     */
    private $lastCommand;

    /**
     * @param DoSomething $aCommand
     */
    public function doSomething(DoSomething $aCommand)
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
