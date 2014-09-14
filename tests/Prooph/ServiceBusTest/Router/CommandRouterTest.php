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
}
 