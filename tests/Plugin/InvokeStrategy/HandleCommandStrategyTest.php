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

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use Prooph\ServiceBus\Plugin\InvokeStrategy\HandleCommandStrategy;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\CustomMessageCommandHandler;
use ProophTest\ServiceBus\Mock\MessageHandler;
use ProophTest\ServiceBus\TestCase;

/**
 * Class HandleCommandStrategyTest
 *
 * @package ProophTest\ServiceBus\Plugin\InvokeStrategy
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
