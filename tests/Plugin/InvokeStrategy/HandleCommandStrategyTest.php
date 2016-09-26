<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use Prooph\ServiceBus\Plugin\InvokeStrategy\HandleCommandStrategy;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\CustomMessageCommandHandler;
use ProophTest\ServiceBus\Mock\CustomMessageWithName;
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

    /**
     * @test
     */
    public function it_determines_the_command_name_from_message_name_call_if_event_has_one()
    {
        $handleCommandStrategy = new HandleCommandStrategy();
        $customCommand = new CustomMessageWithName("I am an event with a messageName() method");

        $closure = function ($command) {
            return $this->determineCommandName($command);
        };
        $determineCommandName = $closure->bindTo($handleCommandStrategy, $handleCommandStrategy);

        $this->assertSame('CustomMessageWithSomeOtherName', $determineCommandName($customCommand));
    }
}
