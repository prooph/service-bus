<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.09.14 - 18:09
 */

namespace Prooph\ServiceBusTest\Process;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class CommandDispatchTest
 *
 * @package Prooph\ServiceBusTest\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandDispatchTest extends TestCase
{
    //@TODO Add tests for initialize with MessageNameProvider and MessageInterface commands

    /**
     * @test
     */
    public function it_is_initialized_with_a_command_and_a_command_bus_as_target()
    {
        $command = new \ArrayObject(array('name' => 'DoSomething'));

        $commandBus = new CommandBus();

        $commandDispatch = CommandDispatch::initializeWith($command, $commandBus);

        $this->assertSame($command, $commandDispatch->getCommand());
        $this->assertSame($commandBus, $commandDispatch->getTarget());
        $this->assertEquals(CommandDispatch::INITIALIZE, $commandDispatch->getName());
    }

    /**
     * @test
     */
    public function it_replaces_command_with_a_new_one()
    {
        $otherCommand = new \ArrayObject(array('name' => 'DoSomethingElse'));

        $commandDispatch = $this->getNewCommandDispatch();

        $commandDispatch->setCommand($otherCommand);

        $this->assertSame($otherCommand, $commandDispatch->getCommand());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_command_name()
    {
        $commandDispatch = $this->getNewCommandDispatch();

        $commandDispatch->setCommandName("DoSomething");

        $this->assertEquals("DoSomething", $commandDispatch->getCommandName());
    }

    /**
     * @test
     */
    public function it_returns_null_when_command_name_is_not_set()
    {
        $this->assertNull($this->getNewCommandDispatch()->getCommandName());
    }

    /**
     * @test
     */
    public function it_only_accepts_a_string_as_command_name()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->getNewCommandDispatch()->setCommandName(123);
    }

    /**
     * @test
     */
    public function it_sets_and_gets_a_command_handler_string()
    {
        $commandDispatch = $this->getNewCommandDispatch();

        $commandDispatch->setCommandHandler("DoSomethingHandler");

        $this->assertEquals("DoSomethingHandler", $commandDispatch->getCommandHandler());
    }

    /**
     * @test
     */
    public function it_accepts_an_object_as_command_handler()
    {
        $commandHandler = new \stdClass();

        $commandDispatch = $this->getNewCommandDispatch();

        $commandDispatch->setCommandHandler($commandHandler);

        $this->assertSame($commandHandler, $commandDispatch->getCommandHandler());
    }

    /**
     * @test
     */
    public function it_accepts_a_callable_as_command_handler()
    {
        $commandHandlerCallback = function ($command) {};

        $commandDispatch = $this->getNewCommandDispatch();

        $commandDispatch->setCommandHandler($commandHandlerCallback);

        $this->assertSame($commandHandlerCallback, $commandDispatch->getCommandHandler());
    }

    /**
     * @test
     */
    public function it_does_not_accept_a_non_callable_array_as_command_handler()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $commandDispatch = $this->getNewCommandDispatch();

        $commandDispatch->setCommandHandler(array("DoSomethingHandler"));
    }

    /**
     * @test
     */
    public function it_has_always_a_logger_available()
    {
        $this->assertInstanceOf('Zend\Log\LoggerInterface', $this->getNewCommandDispatch()->getLogger());
    }

    /**
     * @return CommandDispatch
     */
    protected function getNewCommandDispatch()
    {
        return CommandDispatch::initializeWith(new \ArrayObject(array('name' => 'DoSomething')), new CommandBus());
    }
}
 