<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 20:51
 */

namespace Prooph\ServiceBusTest\Mock;
use Prooph\Common\Messaging\Command;

/**
 * Class DoSomethingHandler
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DoSomethingHandler
{
    /**
     * @var Command
     */
    private $lastCommand;

    /**
     * @param Command $aCommand
     */
    public function doSomething(Command $aCommand)
    {
        $this->lastCommand = $aCommand;
    }

    /**
     * @return Command
     */
    public function lastCommand()
    {
        return $this->lastCommand;
    }
}
