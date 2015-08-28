<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 09/14/14 - 23:51
 */

namespace Prooph\ServiceBusTest\Plugin\Router;

use Prooph\Common\Event\DefaultActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class SingleHandlerRouterTest
 *
 * @package Prooph\ServiceBusTest\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SingleHandlerRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_handle_routing_definition_by_chaining_route_to()
    {
        $router = new CommandRouter();

        $router->route('Prooph\ServiceBusTest\Mock\DoSomething')->to("DoSomethingHandler");

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'Prooph\ServiceBusTest\Mock\DoSomething',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEquals("DoSomethingHandler", $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
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
        $router = new CommandRouter([
            'Prooph\ServiceBusTest\Mock\DoSomething' => 'DoSomethingHandler'
        ]);

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'Prooph\ServiceBusTest\Mock\DoSomething',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEquals("DoSomethingHandler", $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }
}
