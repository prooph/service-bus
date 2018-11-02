<?php

/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\Router;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\AsyncSwitchMessageRouter;
use Prooph\ServiceBus\Plugin\Router\EventRouter;
use Prooph\ServiceBus\Plugin\Router\SingleHandlerRouter;
use ProophTest\ServiceBus\Mock\AsyncCommand;
use ProophTest\ServiceBus\Mock\AsyncEvent;
use ProophTest\ServiceBus\Mock\NonAsyncCommand;

class AsyncSwitchMessageRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_message_producer_as_message_handler_on_dispatch_initialize(): void
    {
        $messageProducer = $this->prophesize(MessageProducer::class);
        $messageProducer = $messageProducer->reveal();

        $commandBus = new CommandBus();

        $handler = null;

        $commandBus->attach(
            CommandBus::EVENT_DISPATCH,
            function (ActionEvent $e) use (&$handler): void {
                $handler = $e->getParam(CommandBus::EVENT_PARAM_MESSAGE_HANDLER);
            },
            CommandBus::PRIORITY_ROUTE - 1
        );

        $router = new AsyncSwitchMessageRouter(new SingleHandlerRouter(), $messageProducer);
        $router->attachToMessageBus($commandBus);

        $message = new AsyncCommand(['foo' => 'bar']);

        $commandBus->dispatch($message);

        $this->assertSame($messageProducer, $handler);
    }

    /**
     * @test
     */
    public function it_returns_early_when_message_name_is_empty(): void
    {
        $messageProducer = $this->prophesize(MessageProducer::class);

        $router = new AsyncSwitchMessageRouter(new SingleHandlerRouter(), $messageProducer->reveal());

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            new CommandBus(),
            [
                //We provide message as array containing a "message_name" key because only in this case the factory plugin
                //gets active
                MessageBus::EVENT_PARAM_MESSAGE => [
                    'message_name' => 'custom-message',
                    'payload' => ['some data'],
                ],
            ]
        );

        $router->onRouteMessage($actionEvent);

        $this->assertEmpty($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }

    /**
     * @test
     */
    public function unmarked_message_is_passed_to_decorated_router()
    {
        $messageProducer = $this->prophesize(MessageProducer::class);
        $decoratedRouter = $this->prophesize(SingleHandlerRouter::class);

        $message = NonAsyncCommand::createCommand('test-data');
        $actionEvent = new DefaultActionEvent(
            AsyncCommand::class,
            new CommandBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => \get_class($message),
                MessageBus::EVENT_PARAM_MESSAGE => $message,
            ]
        );

        $decoratedRouter->onRouteMessage($actionEvent)->shouldBeCalled();

        $router = new AsyncSwitchMessageRouter($decoratedRouter->reveal(), $messageProducer->reveal());
        $router->onRouteMessage($actionEvent);

        $updatedMessage = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $this->assertArrayNotHasKey('handled-async', $updatedMessage->metadata());
    }

    /**
     * @test
     */
    public function marked_message_is_passed_to_async_producer()
    {
        $messageProducer = $this->prophesize(MessageProducer::class);

        $message = AsyncCommand::createCommand('test-data');
        $actionEvent = new DefaultActionEvent(
            AsyncCommand::class,
            new CommandBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => \get_class($message),
                MessageBus::EVENT_PARAM_MESSAGE => $message,
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
        $messageProducer = $this->prophesize(MessageProducer::class);
        $decoratedRouter = $this->prophesize(SingleHandlerRouter::class);

        $message = AsyncCommand::createCommand('test-data');
        $message = $message->withAddedMetadata('handled-async', true);

        $actionEvent = new DefaultActionEvent(
            AsyncCommand::class,
            new CommandBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => \get_class($message),
                MessageBus::EVENT_PARAM_MESSAGE => $message,
            ]
        );

        $decoratedRouter->onRouteMessage($actionEvent)->shouldBeCalled();

        $router = new AsyncSwitchMessageRouter($decoratedRouter->reveal(), $messageProducer->reveal());
        $router->onRouteMessage($actionEvent);

        $updatedMessage = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $this->assertArrayHasKey('handled-async', $updatedMessage->metadata());
        $this->assertTrue($updatedMessage->metadata()['handled-async']);
    }

    /**
     * @test
     */
    public function it_sets_message_producer_as_event_listener_if_target_is_an_event_bus(): void
    {
        $messageProducer = $this->prophesize(MessageProducer::class);

        $message = AsyncEvent::createEvent('test-data');

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            new EventBus(),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => \get_class($message),
                MessageBus::EVENT_PARAM_MESSAGE => $message,
            ]
        );

        $router = new AsyncSwitchMessageRouter(new EventRouter(), $messageProducer->reveal());
        $router->onRouteMessage($actionEvent);

        $this->assertEquals($messageProducer->reveal(), $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS)[0]);

        $updatedMessage = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $this->assertArrayHasKey('handled-async', $updatedMessage->metadata());
        $this->assertTrue($updatedMessage->metadata()['handled-async']);
    }

    /**
     * @test
     */
    public function it_throws_exception_if_target_is_unknown_bus(): void
    {
        $messageProducer = $this->prophesize(MessageProducer::class);

        $message = AsyncEvent::createEvent('test-data');

        $actionEvent = new DefaultActionEvent(
            MessageBus::EVENT_DISPATCH,
            $this->prophesize(MessageBus::class),
            [
                MessageBus::EVENT_PARAM_MESSAGE_NAME => \get_class($message),
                MessageBus::EVENT_PARAM_MESSAGE => $message,
            ]
        );

        $router = new AsyncSwitchMessageRouter(new EventRouter(), $messageProducer->reveal());
        try {
            $router->onRouteMessage($actionEvent);
            $this->fail();
        } catch (RuntimeException $exception) {
        }

        $updatedMessage = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $this->assertArrayHasKey('handled-async', $updatedMessage->metadata());
        $this->assertTrue($updatedMessage->metadata()['handled-async']);
    }
}
