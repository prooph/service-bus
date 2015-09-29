<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 03/09/14 - 21:45
 */

namespace Prooph\ServiceBusTest\Plugin\InvokeStrategy;

use Prooph\ServiceBus\Plugin\InvokeStrategy\HandleCommandStrategy;
use Prooph\ServiceBusTest\Mock\CustomMessage;
use Prooph\ServiceBusTest\Mock\CustomMessageCommandHandler;
use Prooph\ServiceBusTest\Mock\MessageHandler;
use Prooph\ServiceBusTest\TestCase;

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

        $doSomething = new CustomMessage("I am a command");

        $handleCommandHandler = new MessageHandler();

        $this->assertTrue($handleCommandStrategy->canInvoke($handleCommandHandler, $doSomething));

        $handleCommandStrategy->invoke($handleCommandHandler, $doSomething);

        $this->assertSame($doSomething, $handleCommandHandler->getLastMessage());
    }

    /**
     * @test
     */
    public function it_invokes_the_handle_command_method_of_the_handler_without_command_name()
    {
        $handleCommandStrategy = new HandleCommandStrategy();

        $doSomething = new CustomMessage("I am a command");

        $handleCommandHandler = new CustomMessageCommandHandler();

        $this->assertTrue($handleCommandStrategy->canInvoke($handleCommandHandler, $doSomething));

        $handleCommandStrategy->invoke($handleCommandHandler, $doSomething);

        $this->assertSame($doSomething, $handleCommandHandler->getLastMessage());
    }
}
