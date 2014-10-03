<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 23.09.14 - 20:37
 */

namespace Prooph\ServiceBusTest\Router;

use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Process\EventDispatch;
use Prooph\ServiceBus\Router\EventRouter;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class EventRouterTest
 *
 * @package Prooph\ServiceBusTest\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_handle_routing_definition_by_chaining_route_to()
    {
        $router = new EventRouter();

        $router->route('Prooph\ServiceBusTest\Mock\SomethingDone')->to("SomethingDoneListener");

        $eventDispatch = EventDispatch::initializeWith(SomethingDone::getNew(), new EventBus());

        $eventDispatch->setName(EventDispatch::ROUTE);

        $router->onRouteEvent($eventDispatch);

        $this->assertEquals("SomethingDoneListener", $eventDispatch->getEventListeners()[0]);
    }

    /**
     * @test
     */
    public function it_fails_on_routing_a_second_event_before_first_event_is_routed_at_least_to_one_listener()
    {
        $router = new EventRouter();

        $router->route('Prooph\ServiceBusTest\Mock\SomethingDone');

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $router->route('AnotherEvent');
    }

    /**
     * @test
     */
    public function it_can_route_a_second_event_after_the_first_one_is_routed_to_at_least_one_listener()
    {
        $router = new EventRouter();

        $router->route('Prooph\ServiceBusTest\Mock\SomethingDone')->to('a_listener');

        $router->route('AnotherEvent');

        //no exception occurred
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_fails_on_setting_a_listener_before_an_event_is_set()
    {
        $router = new EventRouter();

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $router->to('SomethingDoneListener');
    }

    /**
     * @test
     */
    public function it_takes_a_routing_definition_with_a_single_listener_on_instantiation()
    {
        $router = new EventRouter(array(
            'Prooph\ServiceBusTest\Mock\SomethingDone' => 'SomethingDoneListener'
        ));

        $eventDispatch = EventDispatch::initializeWith(SomethingDone::getNew(), new EventBus());

        $eventDispatch->setName(EventDispatch::ROUTE);

        $router->onRouteEvent($eventDispatch);

        $this->assertEquals("SomethingDoneListener", $eventDispatch->getEventListeners()[0]);
    }

    /**
     * @test
     */
    public function it_takes_a_routing_definition_with_a_multiple_listeners_on_instantiation()
    {
        $router = new EventRouter(array(
            'Prooph\ServiceBusTest\Mock\SomethingDone' => ['SomethingDoneListener1', 'SomethingDoneListener2']
        ));

        $eventDispatch = EventDispatch::initializeWith(SomethingDone::getNew(), new EventBus());

        $eventDispatch->setName(EventDispatch::ROUTE);

        $router->onRouteEvent($eventDispatch);

        $this->assertEquals("SomethingDoneListener1", $eventDispatch->getEventListeners()[0]);
        $this->assertEquals("SomethingDoneListener2", $eventDispatch->getEventListeners()[1]);
    }
}
 