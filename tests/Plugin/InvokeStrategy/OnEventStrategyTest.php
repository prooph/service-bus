<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\DefaultListenerHandler;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\EventListenerException;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\Plugin\InvokeStrategy\OnEventStrategy;
use Prooph\ServiceBus\Plugin\ListenerExceptionCollectionMode;
use Prooph\ServiceBus\Plugin\Router\EventRouter;
use ProophTest\ServiceBus\Mock\CustomInvokableMessageHandler;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\CustomMessageEventHandler;
use ProophTest\ServiceBus\Mock\CustomMessageEventHandler2;
use ProophTest\ServiceBus\Mock\CustomMessageEventHandlerThrowingExceptions;
use ProophTest\ServiceBus\Mock\CustomOnEventStrategy;
use Prophecy\Argument;

class OnEventStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_invokes_the_on_event_method_of_the_handler(): void
    {
        $eventBus = new EventBus();

        $onEventStrategy = new OnEventStrategy();
        $onEventStrategy->attachToMessageBus($eventBus);

        $onEventHandler = new CustomMessageEventHandler();

        $eventRouter = new EventRouter([
            'ProophTest\ServiceBus\Mock\CustomMessage' => $onEventHandler,
        ]);
        $eventRouter->attachToMessageBus($eventBus);

        $customEvent = new CustomMessage('I am an event');
        $eventBus->dispatch($customEvent);

        $this->assertSame($customEvent, $onEventHandler->getLastMessage());
        $this->assertSame(1, $onEventHandler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_can_be_attached_to_event_bus(): void
    {
        $onEventStrategy = new OnEventStrategy();

        $bus = $this->prophesize(EventBus::class);
        $bus->attach(Argument::type('string'), Argument::type('callable'), Argument::type('integer'))
            ->shouldBeCalled()
            ->willReturn(
                new DefaultListenerHandler(
                    function (): void {
                    }
                )
            );

        $onEventStrategy->attachToMessageBus($bus->reveal());
    }

    /**
     * @test
     */
    public function it_should_not_handle_already_processed_messages(): void
    {
        $eventBus = new EventBus();

        $onEventStrategy = new OnEventStrategy();
        $onEventStrategy->attachToMessageBus($eventBus);

        $callableHandler = new CustomInvokableMessageHandler();

        $eventRouter = new EventRouter([
            'ProophTest\ServiceBus\Mock\CustomMessage' => $callableHandler,
        ]);
        $eventRouter->attachToMessageBus($eventBus);

        $customEvent = new CustomMessage('I am an event');
        $eventBus->dispatch($customEvent);

        $this->assertSame($customEvent, $callableHandler->getLastMessage());
        $this->assertSame(1, $callableHandler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_should_still_work_with_callables(): void
    {
        $eventBus = new EventBus();

        $onEventStrategy = new OnEventStrategy();
        $onEventStrategy->attachToMessageBus($eventBus);

        $handler = new CustomMessageEventHandler();

        $result = false;

        $router = new EventRouter();
        $router->route(CustomMessage::class)
            ->to(function (CustomMessage $message) use (&$result): void {
                $result = true;
            })
            ->andTo($handler);

        $router->attachToMessageBus($eventBus);

        $eventBus->dispatch(new CustomMessage('some text'));

        $this->assertTrue($result);
        $this->assertSame(1, $handler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_should_still_work_with_callables_and_collect_all_exceptions(): void
    {
        $eventBus = new EventBus();

        $exceptionModePlugin = new ListenerExceptionCollectionMode();
        $exceptionModePlugin->attachToMessageBus($eventBus);

        $onEventStrategy = new OnEventStrategy();
        $onEventStrategy->attachToMessageBus($eventBus);

        $handler = new CustomMessageEventHandlerThrowingExceptions();

        $router = new EventRouter();
        $router->route(CustomMessage::class)
            ->to(function (CustomMessage $message): void {
                throw new \Exception('foo');
            })
            ->andTo($handler);

        $router->attachToMessageBus($eventBus);

        $ex = null;

        try {
            $eventBus->dispatch(new CustomMessage('some text'));
        } catch (MessageDispatchException $ex) {
            $ex = $ex->getPrevious();
        }

        $this->assertNotNull($ex);
        $this->assertInstanceOf(EventListenerException::class, $ex);
        $this->assertCount(2, $ex->listenerExceptions());
    }

    /**
     * @test
     */
    public function it_should_still_work_with_callables_and_collect_all_exceptions_part2(): void
    {
        $eventBus = new EventBus();

        $exceptionModePlugin = new ListenerExceptionCollectionMode();
        $exceptionModePlugin->attachToMessageBus($eventBus);

        $onEventStrategy = new OnEventStrategy();
        $onEventStrategy->attachToMessageBus($eventBus);

        $handler = new CustomMessageEventHandlerThrowingExceptions();

        $router = new EventRouter();
        $router->route(CustomMessage::class)
            ->to(function (CustomMessage $message): void {
                throw new \Exception('foo');
            })
            ->andTo($handler)
            ->andTo($handler);

        $router->attachToMessageBus($eventBus);

        $ex = null;

        try {
            $eventBus->dispatch(new CustomMessage('some text'));
        } catch (MessageDispatchException $ex) {
            $ex = $ex->getPrevious();
        }

        $this->assertNotNull($ex);
        $this->assertInstanceOf(EventListenerException::class, $ex);
        $this->assertCount(3, $ex->listenerExceptions());
    }

    /**
     * @test
     */
    public function it_should_still_work_with_callables_and_other_strategies(): void
    {
        $eventBus = new EventBus();

        $onEventStrategy = new OnEventStrategy();
        $onEventStrategy->attachToMessageBus($eventBus);

        $secondOnEventStrategy = new CustomOnEventStrategy();
        $secondOnEventStrategy->attachToMessageBus($eventBus);

        $handler = new CustomMessageEventHandler();
        $handler2 = new CustomMessageEventHandler2();

        $result = false;

        $router = new EventRouter();
        $router->route(CustomMessage::class)
            ->to(function (CustomMessage $message) use (&$result): void {
                $result = true;
            })
            ->andTo($handler)
            ->andTo($handler2);

        $router->attachToMessageBus($eventBus);

        $eventBus->dispatch(new CustomMessage('some text'));

        $this->assertTrue($result);
        $this->assertSame(1, $handler->getInvokeCounter());
        $this->assertSame(1, $handler2->getInvokeCounter());
    }
}
