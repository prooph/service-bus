<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.10.14 - 22:47
 */

namespace Prooph\ServiceBusTest\Router;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Process\EventDispatch;
use Prooph\ServiceBus\Router\RegexRouter;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class RegexRouterTest
 *
 * @package Prooph\ServiceBusTest\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class RegexRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_matches_pattern_with_command_name_to_detect_appropriate_handler()
    {
        $regexRouter = new RegexRouter();

        $regexRouter->route('/^'.preg_quote('Prooph\ServiceBusTest\Mock\Do').'.*/')->to("DoSomethingHandler");

        $commandDispatch = CommandDispatch::initializeWith(DoSomething::getNew(), new CommandBus());

        $commandDispatch->setName(CommandDispatch::ROUTE);

        $regexRouter->onRoute($commandDispatch);

        $this->assertEquals("DoSomethingHandler", $commandDispatch->getCommandHandler());
    }

    /**
     * @test
     */
    public function it_does_not_allow_that_two_pattern_matches_with_same_command_name()
    {
        $regexRouter = new RegexRouter();

        $regexRouter->route('/^'.preg_quote('Prooph\ServiceBusTest\Mock\Do').'.*/')->to("DoSomethingHandler");
        $regexRouter->route('/^'.preg_quote('Prooph\ServiceBusTest\Mock\\').'.*/')->to("DoSomethingHandler2");

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $commandDispatch = CommandDispatch::initializeWith(DoSomething::getNew(), new CommandBus());

        $commandDispatch->setName(CommandDispatch::ROUTE);

        $regexRouter->onRoute($commandDispatch);
    }

    /**
     * @test
     */
    public function it_matches_pattern_with_event_name_and_routes_to_multiple_listeners()
    {
        $regexRouter = new RegexRouter();

        $regexRouter->route('/^'.preg_quote('Prooph\ServiceBusTest\Mock\\').'.*Done$/')->to("SomethingDoneListener1");
        $regexRouter->route('/^'.preg_quote('Prooph\ServiceBusTest\Mock\\').'.*Done$/')->to("SomethingDoneListener2");

        $eventDispatch = EventDispatch::initializeWith(SomethingDone::getNew(), new EventBus());

        $eventDispatch->setName(EventDispatch::ROUTE);

        $regexRouter->onRoute($eventDispatch);

        $this->assertEquals(["SomethingDoneListener1", "SomethingDoneListener2"], $eventDispatch->getEventListeners()->getArrayCopy());
    }

    /**
     * @test
     */
    public function it_fails_on_routing_a_second_pattern_before_first_definition_is_finished()
    {
        $router = new RegexRouter();

        $router->route('Prooph\ServiceBusTest\Mock\DoSomething');

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $router->route('/.*/');
    }

    /**
     * @test
     */
    public function it_fails_on_setting_a_handler_before_a_pattern_is_set()
    {
        $router = new RegexRouter();

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $router->to('DoSomethingHandler');
    }

    /**
     * @test
     */
    public function it_takes_a_routing_definition_on_instantiation()
    {
        $router = new RegexRouter(array(
            '/^'.preg_quote('Prooph\ServiceBusTest\Mock\Do').'.*/' => 'DoSomethingHandler',
            '/^'.preg_quote('Prooph\ServiceBusTest\Mock\\').'.*Done$/' => ["SomethingDoneListener1", "SomethingDoneListener2"]

        ));

        $commandDispatch = CommandDispatch::initializeWith(DoSomething::getNew(), new CommandBus());

        $commandDispatch->setName(CommandDispatch::ROUTE);

        $router->onRoute($commandDispatch);

        $this->assertEquals("DoSomethingHandler", $commandDispatch->getCommandHandler());

        $eventDispatch = EventDispatch::initializeWith(SomethingDone::getNew(), new EventBus());

        $eventDispatch->setName(EventDispatch::ROUTE);

        $router->onRoute($eventDispatch);

        $this->assertEquals(["SomethingDoneListener1", "SomethingDoneListener2"], $eventDispatch->getEventListeners()->getArrayCopy());
    }
}
 