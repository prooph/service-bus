<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 21:45
 */

namespace Prooph\ServiceBusTest\InvokeStrategy;

use Prooph\ServiceBus\InvokeStrategy\HandleCommandStrategy;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\HandleCommandHandler;
use Prooph\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class HandleCommandStrategyTest
 *
 * @package Prooph\ServiceBusTest\InvokeStrategy
 * @author Alexander Miertsch <contact@prooph.de>
 */
class HandleCommandStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_invokes_the_handle_command_method_of_the_handler()
    {
        $handleCommandStrategy = new HandleCommandStrategy();

        $doSomething = DoSomething::fromData('test payload');

        $handleCommandHandler = new HandleCommandHandler();

        $this->assertTrue($handleCommandStrategy->canInvoke($handleCommandHandler, $doSomething));

        $handleCommandStrategy->invoke($handleCommandHandler, $doSomething);

        $this->assertEquals('test payload', $handleCommandHandler->lastCommand()->data());
    }
}
