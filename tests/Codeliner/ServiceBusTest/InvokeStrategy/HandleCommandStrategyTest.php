<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 21:45
 */

namespace Codeliner\ServiceBusTest\InvokeStrategy;

use Codeliner\ServiceBus\InvokeStrategy\HandleCommandStrategy;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\Mock\HandleCommandHandler;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class HandleCommandStrategyTest
 *
 * @package Codeliner\ServiceBusTest\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
