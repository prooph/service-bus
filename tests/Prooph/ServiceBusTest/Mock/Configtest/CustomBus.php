<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 21:05
 */

namespace Prooph\ServiceBusTest\Mock\Configtest;

use Prooph\ServiceBus\Command\CommandBus;

class CustomBus extends CommandBus
{
    public $message;

    public function __construct($message) {
        $this->message = $message;
    }
}
 