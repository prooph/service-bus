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

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\InvalidArgumentException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\EventRouter;

class EventRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_handle_routing_definition_by_chaining_route_to(): void
    {
        $router = new EventRouter();

        $router->route('SomethingDone')->to('SomethingDoneListener1')->andTo('SomethingDoneListener2');

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            new EventBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => 'SomethingDone',
            ],
            MessageBus::PRIORITY_ROUTE
        );

        $router->onRouteMessage($actionEvent);

        $listeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS);

        $this->assertCount(2, $listeners);
        $this->assertEquals('SomethingDoneListener1', $listeners[0]);
        $this->assertEquals('SomethingDoneListener2', $listeners[1]);
    }

    /**
     * @test
     */
    public function it_fails_on_routing_a_second_event_before_first_event_is_routed_at_least_to_one_listener(): void
    {
        $this->expectException(RuntimeException::class);

        $router = new EventRouter();

        $router->route('SomethingDone');

        $router->route('AnotherEvent');
    }

    /**
     * @test
     */
    public function it_can_route_a_second_event_after_the_first_one_is_routed_to_at_least_one_listener(): void
    {
        $router = new EventRouter();

        $router->route('SomethingDone')->to('a_listener');

        $router->route('AnotherEvent');

        //no exception occurred
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_fails_on_setting_a_listener_before_an_event_is_set(): void
    {
        $this->expectException(RuntimeException::class);

        $router = new EventRouter();

        $router->to('SomethingDoneListener');
    }

    /**
     * @test
     */
    public function it_fails_on_setting_an_invalid_listener(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $router = new EventRouter();
        $router->to(null);
    }

    /**
     * @test
     */
    public function it_takes_a_routing_definition_with_a_single_listener_on_instantiation(): void
    {
        $router = new EventRouter([
            'SomethingDone' => 'SomethingDoneListener',
        ]);

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            new EventBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => 'SomethingDone',
            ],
            MessageBus::PRIORITY_ROUTE
        );

        $router->onRouteMessage($actionEvent);

        $this->assertEquals('SomethingDoneListener', $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS)[0]);
    }

    /**
     * @test
     */
    public function it_takes_a_routing_definition_with_a_multiple_listeners_on_instantiation(): void
    {
        $router = new EventRouter([
            'SomethingDone' => ['SomethingDoneListener1', 'SomethingDoneListener2'],
        ]);

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            new EventBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => 'SomethingDone',
            ],
            MessageBus::PRIORITY_ROUTE
        );

        $router->onRouteMessage($actionEvent);

        $this->assertEquals('SomethingDoneListener1', $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS)[0]);
        $this->assertEquals('SomethingDoneListener2', $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS)[1]);
    }

    /**
     * @test
     */
    public function it_still_works_if_deprecated_method_on_route_event_is_used(): void
    {
        $router = new EventRouter([
            'SomethingDone' => ['SomethingDoneListener1', 'SomethingDoneListener2'],
        ]);

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            new EventBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => 'SomethingDone',
            ],
            MessageBus::PRIORITY_ROUTE
        );

        $router->onRouteMessage($actionEvent);

        $this->assertEquals('SomethingDoneListener1', $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS)[0]);
        $this->assertEquals('SomethingDoneListener2', $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS)[1]);
    }

    /**
     * @test
     */
    public function it_returns_early_on_route_event_when_message_name_is_empty(): void
    {
        $router = new EventRouter([
            'SomethingDone' => ['SomethingDoneListener1', 'SomethingDoneListener2'],
        ]);

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            new EventBus(),
            [
                '' => 'SomethingDone',
            ],
            MessageBus::PRIORITY_ROUTE
        );

        $router->onRouteMessage($actionEvent);

        $listeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS);
        $this->assertEmpty($listeners);
    }

    /**
     * @test
     */
    public function it_returns_early_on_route_event_when_message_name_is_not_in_event_map(): void
    {
        $router = new EventRouter([
            'SomethingDone' => ['SomethingDoneListener1', 'SomethingDoneListener2'],
        ]);

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            new EventBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => 'unknown',
            ],
            MessageBus::PRIORITY_ROUTE
        );

        $router->onRouteMessage($actionEvent);

        $listeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS);
        $this->assertEmpty($listeners);
    }
}
