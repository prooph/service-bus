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
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\RegexRouter;
use ProophTest\ServiceBus\TestCase;

/**
 * Class RegexRouterTest
 *
 * @package ProophTest\ServiceBus\Plugin\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class RegexRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_matches_pattern_with_command_name_to_detect_appropriate_handler() : void
    {
        $regexRouter = new RegexRouter();

        $regexRouter->route('/^'.preg_quote('ProophTest\ServiceBus\Mock\Do').'.*/')->to("DoSomethingHandler");

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\DoSomething',
        ]);

        $regexRouter->onRouteMessage($actionEvent);

        $this->assertEquals("DoSomethingHandler", $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }

    /**
     * @test
     */
    public function it_returns_early_when_message_name_is_empty() : void
    {
        $regexRouter = new RegexRouter();

        $regexRouter->route('/^'.preg_quote('ProophTest\ServiceBus\Mock\Do').'.*/')->to("DoSomethingHandler");

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            '' => 'ProophTest\ServiceBus\Mock\DoSomething',
        ]);

        $regexRouter->onRouteMessage($actionEvent);

        $this->assertEmpty($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }

    /**
     * @test
     */
    public function it_does_not_allow_that_two_pattern_matches_with_same_command_name() : void
    {
        $regexRouter = new RegexRouter();

        $regexRouter->route('/^'.preg_quote('ProophTest\ServiceBus\Mock\Do').'.*/')->to("DoSomethingHandler");
        $regexRouter->route('/^'.preg_quote('ProophTest\ServiceBus\Mock\\').'.*/')->to("DoSomethingHandler2");

        $this->setExpectedException('\Prooph\ServiceBus\Exception\RuntimeException');

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\DoSomething',
        ]);

        $regexRouter->onRouteMessage($actionEvent);
    }

    /**
     * @test
     */
    public function it_matches_pattern_with_event_name_and_routes_to_multiple_listeners() : void
    {
        $regexRouter = new RegexRouter();

        $regexRouter->route('/^'.preg_quote('ProophTest\ServiceBus\Mock\\').'.*Done$/')->to("SomethingDoneListener1");
        $regexRouter->route('/^'.preg_quote('ProophTest\ServiceBus\Mock\\').'.*Done$/')->to("SomethingDoneListener2");

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new EventBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\SomethingDone',
        ]);

        $regexRouter->onRouteMessage($actionEvent);

        $this->assertEquals(["SomethingDoneListener1", "SomethingDoneListener2"], $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS));
    }

    /**
     * @test
     */
    public function it_returns_early_when_message_name_is_empty_on_multiple_listeners() : void
    {
        $regexRouter = new RegexRouter();

        $regexRouter->route('/^'.preg_quote('ProophTest\ServiceBus\Mock\\').'.*Done$/')->to("SomethingDoneListener1");
        $regexRouter->route('/^'.preg_quote('ProophTest\ServiceBus\Mock\\').'.*Done$/')->to("SomethingDoneListener2");

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new EventBus(), [
            '' => 'ProophTest\ServiceBus\Mock\SomethingDone',
        ]);

        $regexRouter->onRouteMessage($actionEvent);

        $this->assertEmpty($actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS));
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_fails_on_routing_a_second_pattern_before_first_definition_is_finished() : void
    {
        $router = new RegexRouter();

        $router->route('ProophTest\ServiceBus\Mock\DoSomething');

        $router->route('/.*/');
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_fails_on_setting_a_handler_before_a_pattern_is_set() : void
    {
        $router = new RegexRouter();

        $router->to('DoSomethingHandler');
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\InvalidArgumentException
     */
    public function it_fails_when_routing_to_invalid_handler() : void
    {
        $router = new RegexRouter();

        $router->to(null);
    }

    /**
     * @test
     */
    public function it_takes_a_routing_definition_on_instantiation() : void
    {
        $router = new RegexRouter([
            '/^'.preg_quote('ProophTest\ServiceBus\Mock\Do').'.*/' => 'DoSomethingHandler',
            '/^'.preg_quote('ProophTest\ServiceBus\Mock\\').'.*Done$/' => ["SomethingDoneListener1", "SomethingDoneListener2"]

        ]);

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\DoSomething',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEquals("DoSomethingHandler", $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new EventBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\SomethingDone',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEquals(["SomethingDoneListener1", "SomethingDoneListener2"], $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS));
    }

    /**
     * @test
     */
    public function it_still_works_if_deprecated_method_on_route_is_used() : void
    {
        $router = new RegexRouter([
            '/^'.preg_quote('ProophTest\ServiceBus\Mock\Do').'.*/' => 'DoSomethingHandler',
            '/^'.preg_quote('ProophTest\ServiceBus\Mock\\').'.*Done$/' => ["SomethingDoneListener1", "SomethingDoneListener2"]

        ]);

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\DoSomething',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEquals("DoSomethingHandler", $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_ROUTE, new EventBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_NAME => 'ProophTest\ServiceBus\Mock\SomethingDone',
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEquals(["SomethingDoneListener1", "SomethingDoneListener2"], $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS));
    }
}
