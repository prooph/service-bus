<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\Router;

use Prooph\Common\Event\DefaultActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use ProophTest\ServiceBus\TestCase;

/**
 * Class SingleHandlerRouterTest
 *
 * @package ProophTest\ServiceBus\Plugin\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SingleHandlerRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_handle_routing_definition_by_chaining_route_to(): void
    {
        $router = new CommandRouter();

        $router->route('ProophTest\ServiceBus\Mock\DoSomething')->to("DoSomethingHandler");

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\DoSomething',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEquals("DoSomethingHandler", $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\InvalidArgumentException
     */
    public function it_fails_when_routing_to_invalid_handler(): void
    {
        $router = new CommandRouter();

        $router->route('ProophTest\ServiceBus\Mock\DoSomething')->to(null);
    }

    /**
     * @test
     */
    public function it_returns_early_when_message_name_is_empty(): void
    {
        $router = new CommandRouter();

        $router->route('ProophTest\ServiceBus\Mock\DoSomething')->to("DoSomethingHandler");

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'unknown',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEmpty($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }

    /**
     * @test
     */
    public function it_returns_early_when_message_name_is_not_in_event_map(): void
    {
        $router = new CommandRouter();

        $router->route('ProophTest\ServiceBus\Mock\DoSomething')->to("DoSomethingHandler");

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            '' => 'ProophTest\ServiceBus\Mock\DoSomething',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEmpty($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }

    /**
     * @test
     */
    public function it_fails_on_routing_a_second_command_before_first_definition_is_finished(): void
    {
        $router = new CommandRouter();

        $router->route('ProophTest\ServiceBus\Mock\DoSomething');

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $router->route('AnotherCommand');
    }

    /**
     * @test
     */
    public function it_fails_on_setting_a_handler_before_a_command_is_set(): void
    {
        $router = new CommandRouter();

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $router->to('DoSomethingHandler');
    }

    /**
     * @test
     */
    public function it_takes_a_routing_definition_on_instantiation(): void
    {
        $router = new CommandRouter([
            'ProophTest\ServiceBus\Mock\DoSomething' => 'DoSomethingHandler'
        ]);

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\DoSomething',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEquals("DoSomethingHandler", $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }
}
