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

namespace ProophTest\ServiceBus\Plugin\Router;

use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\Common\Event\ListenerHandler;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\AsyncSwitchMessageRouter;
use Prooph\ServiceBus\Plugin\Router\MessageBusRouterPlugin;
use Prooph\ServiceBus\Plugin\Router\SingleHandlerRouter;
use ProophTest\ServiceBus\Mock\AsyncCommand;
use ProophTest\ServiceBus\Mock\NonAsyncCommand;
use ProophTest\ServiceBus\TestCase;

/**
 * Class SingleHandlerRouterTest
 *
 * @package ProophTest\ServiceBus\Plugin\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AsyncSwitchMessageRouterTest extends TestCase
{

    /**
     * @test
     */
    public function it_sets_message_producer_as_message_handler_on_dispatch_initialize()
    {
        $actionEventEmitter = $this->prophesize(ActionEventEmitter::class);
        $listenerHandler = $this->prophesize(ListenerHandler::class);

        $messageProducer = $this->prophesize(MessageBusRouterPlugin::class);

        $router = new AsyncSwitchMessageRouter(new SingleHandlerRouter(), $messageProducer->reveal());

        $actionEventEmitter
            ->attachListener(MessageBus::EVENT_ROUTE, [$router, 'onRouteMessage'])
            ->willReturn($listenerHandler->reveal())
            ->shouldBeCalled();

        $router->attach($actionEventEmitter->reveal());
    }


    /**
     * @test
     */
    public function it_returns_early_when_message_name_is_empty()
    {
        $messageProducer = $this->prophesize(MessageBusRouterPlugin::class);

        $router = new AsyncSwitchMessageRouter(new SingleHandlerRouter(), $messageProducer->reveal());

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_INITIALIZE, new CommandBus(), [
            //We provide message as array containing a "message_name" key because only in this case the factory plugin
            //gets active
            MessageBus::EVENT_PARAM_MESSAGE => [
                'message_name' => 'custom-message',
                'payload' => ["some data"]
            ]
        ]);

        $router->onRouteMessage($actionEvent);

        $this->assertEmpty($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }

    /**
     * @test
     */
    public function unmarked_message_is_passed_to_decorated_router()
    {
        $messageProducer = $this->prophesize(MessageBusRouterPlugin::class);
        $decoratedRouter = $this->prophesize(SingleHandlerRouter::class);

        $message = NonAsyncCommand::createCommand('test-data');
        $actionEvent = new DefaultActionEvent(
            AsyncCommand::class,
            null,
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => get_class($message),
                MessageBus::EVENT_PARAM_MESSAGE => $message
            ]
        );

        $decoratedRouter->onRouteMessage($actionEvent)->willReturn('handled-by-decorated-router');

        $router = new AsyncSwitchMessageRouter($decoratedRouter->reveal(), $messageProducer->reveal());
        $rtn = $router->onRouteMessage($actionEvent);

        $this->assertEquals('handled-by-decorated-router', $rtn);
        $updatedMessage = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $this->assertArrayNotHasKey('handled-async', $updatedMessage->metadata());
    }

    /**
    * @test
    */
    public function marked_message_is_passed_to_async_producer()
    {
        $messageProducer = $this->prophesize(MessageBusRouterPlugin::class);

        $message = AsyncCommand::createCommand('test-data');
        $actionEvent = new DefaultActionEvent(
            AsyncCommand::class,
            null,
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => get_class($message),
                MessageBus::EVENT_PARAM_MESSAGE => $message
            ]
        );

        $router = new AsyncSwitchMessageRouter(new SingleHandlerRouter(), $messageProducer->reveal());
        $router->onRouteMessage($actionEvent);

        $this->assertEquals($messageProducer->reveal(), $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));

        $updatedMessage = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $this->assertArrayHasKey('handled-async', $updatedMessage->metadata());
        $this->assertTrue($updatedMessage->metadata()['handled-async']);
    }

    /**
     * @test
     */
    public function marked_message_is_passed_to_decorated_router_as_already_handled_by_async_provider()
    {
        $messageProducer = $this->prophesize(MessageBusRouterPlugin::class);
        $decoratedRouter = $this->prophesize(SingleHandlerRouter::class);

        $message = AsyncCommand::createCommand('test-data');
        $message = $message->withAddedMetadata('handled-async', true);

        $actionEvent = new DefaultActionEvent(
            AsyncCommand::class,
            null,
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => get_class($message),
                MessageBus::EVENT_PARAM_MESSAGE => $message
            ]
        );

        $decoratedRouter->onRouteMessage($actionEvent)->willReturn('handled-by-decorated-router');

        $router = new AsyncSwitchMessageRouter($decoratedRouter->reveal(), $messageProducer->reveal());
        $rtn = $router->onRouteMessage($actionEvent);

        $this->assertEquals('handled-by-decorated-router', $rtn);
        $updatedMessage = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $this->assertArrayHasKey('handled-async', $updatedMessage->metadata());
        $this->assertTrue($updatedMessage->metadata()['handled-async']);
    }
}
