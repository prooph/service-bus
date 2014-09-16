<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.09.14 - 23:51
 */

namespace Prooph\ServiceBusTest\Router;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Router\CommandRouter;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class CommandRouterTest
 *
 * @package Prooph\ServiceBusTest\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_handle_routing_definition_by_chaining_route_to()
    {
        $router = new CommandRouter();

        $router->route('Prooph\ServiceBusTest\Mock\DoSomething')->to("DoSomethingHandler");

        $commandDispatch = CommandDispatch::initializeWith(DoSomething::getNew(), new CommandBus());

        $commandDispatch->setName(CommandDispatch::ROUTE);

        $router->onRouteEvent($commandDispatch);

        $this->assertEquals("DoSomethingHandler", $commandDispatch->getCommandHandler());
    }

    /**
     * @test
     */
    public function it_fails_on_routing_a_second_command_before_first_definition_is_finished()
    {
        $router = new CommandRouter();

        $router->route('Prooph\ServiceBusTest\Mock\DoSomething');

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $router->route('AnotherCommand');
    }

    /**
     * @test
     */
    public function it_fails_on_setting_a_handler_before_a_command_is_set()
    {
        $router = new CommandRouter();

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $router->to('DoSomethingHandler');
    }

    /**
     * @test
     */
    public function it_takes_a_routing_definition_on_instantiation()
    {
        $router = new CommandRouter(array(
            'Prooph\ServiceBusTest\Mock\DoSomething' => 'DoSomethingHandler'
        ));

        $commandDispatch = CommandDispatch::initializeWith(DoSomething::getNew(), new CommandBus());

        $commandDispatch->setName(CommandDispatch::ROUTE);

        $router->onRouteEvent($commandDispatch);

        $this->assertEquals("DoSomethingHandler", $commandDispatch->getCommandHandler());
    }
}
 