<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\EventListenerException;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\MessageBus;
use ProophTest\ServiceBus\Mock\AutoErrorProducer;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\ErrorProducer;
use ProophTest\ServiceBus\Mock\MessageHandler;
use ProophTest\ServiceBus\Mock\SomethingDone;

class EventBusTest extends TestCase
{
    /**
     * @var EventBus
     */
    private $eventBus;

    protected function setUp()
    {
        $this->eventBus = new EventBus();
    }

    /**
     * @test
     */
    public function it_dispatches_a_message_using_the_default_process(): void
    {
        $somethingDone = new SomethingDone(['done' => 'bought milk']);

        $receivedMessage = null;
        $dispatchEvent = null;
        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$receivedMessage, &$dispatchEvent): void {
                $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [
                    function (SomethingDone $somethingDone) use (&$receivedMessage): void {
                        $receivedMessage = $somethingDone;
                    },
                ]);

                $dispatchEvent = $actionEvent;
            },
            MessageBus::PRIORITY_ROUTE
        );

        $this->eventBus->dispatch($somethingDone);

        $this->assertSame($somethingDone, $receivedMessage);
        $this->assertTrue($dispatchEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED));
    }

    /**
     * @test
     */
    public function it_triggers_all_defined_action_events(): void
    {
        $initializeIsTriggered = false;
        $detectMessageNameIsTriggered = false;
        $routeIsTriggered = false;
        $locateHandlerIsTriggered = false;
        $invokeHandlerIsTriggered = false;
        $finalizeIsTriggered = false;

        //Should always be triggered
        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$initializeIsTriggered): void {
                $initializeIsTriggered = true;
            },
            MessageBus::PRIORITY_INITIALIZE
        );

        //Should be triggered because we dispatch a message that does not
        //implement Prooph\Common\Messaging\HasMessageName
        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$detectMessageNameIsTriggered): void {
                $detectMessageNameIsTriggered = true;
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, 'custom-message');
            },
            MessageBus::PRIORITY_DETECT_MESSAGE_NAME
        );

        //Should be triggered because we did not provide a message-handler yet
        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$routeIsTriggered): void {
                $routeIsTriggered = true;
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === 'custom-message') {
                    //We provide the message handler as a string (service id) to tell the bus to trigger the locate-handler event
                    $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, ['error-producer']);
                }
            },
            MessageBus::PRIORITY_ROUTE
        );

        //Should be triggered because we provided the message-handler as string (service id)
        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$locateHandlerIsTriggered): void {
                $locateHandlerIsTriggered = true;
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER) === 'error-producer') {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, new ErrorProducer());
                }
            },
            MessageBus::PRIORITY_LOCATE_HANDLER
        );

        //Should be triggered because the message-handler is not callable
        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$invokeHandlerIsTriggered): void {
                $invokeHandlerIsTriggered = true;
                $handler = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);
                if ($handler instanceof ErrorProducer) {
                    $handler->throwException($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE));
                }
            },
            MessageBus::PRIORITY_INVOKE_HANDLER
        );

        //Should always be triggered
        $this->eventBus->attach(
            MessageBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$finalizeIsTriggered): void {
                $finalizeIsTriggered = true;
            }
        );

        $customMessage = new CustomMessage('I have no further meaning');

        $this->eventBus->dispatch($customMessage);

        $this->assertTrue($initializeIsTriggered);
        $this->assertTrue($detectMessageNameIsTriggered);
        $this->assertTrue($routeIsTriggered);
        $this->assertTrue($locateHandlerIsTriggered);
        $this->assertTrue($invokeHandlerIsTriggered);
        $this->assertTrue($finalizeIsTriggered);
    }

    /**
     * @test
     */
    public function it_uses_the_fqcn_of_the_message_if_message_name_was_not_provided_and_message_does_not_implement_has_message_name(): void
    {
        $handler = new MessageHandler();

        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $e) use ($handler): void {
                if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                    $e->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [$handler]);
                }
            },
            MessageBus::PRIORITY_ROUTE
        );

        $customMessage = new CustomMessage('foo');

        $this->eventBus->dispatch($customMessage);

        $this->assertSame($customMessage, $handler->getLastMessage());
    }

    /**
     * @test
     */
    public function it_throws_service_bus_exception_if_exception_is_not_handled_by_a_plugin(): void
    {
        $this->expectException(MessageDispatchException::class);

        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function () {
                throw new \Exception('ka boom');
            },
            MessageBus::PRIORITY_INITIALIZE
        );

        $this->eventBus->dispatch('throw it');
    }

    /**
     * @test
     */
    public function it_invokes_all_listeners(): void
    {
        $handler = new MessageHandler();

        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $e) use ($handler): void {
                if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                    $e->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [$handler, $handler]);
                }
            },
            MessageBus::PRIORITY_ROUTE
        );

        $customMessage = new CustomMessage('foo');

        $this->eventBus->dispatch($customMessage);

        $this->assertSame($customMessage, $handler->getLastMessage());
        $this->assertEquals(2, $handler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_stops_by_default_if_listener_throws_an_exception(): void
    {
        $handler = new MessageHandler();
        $errorProducer = new AutoErrorProducer();
        $finalizeIsTriggered = false;

        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $e) use ($handler, $errorProducer): void {
                if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                    $e->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [$handler, $errorProducer,  $handler]);
                }
            },
            MessageBus::PRIORITY_ROUTE
        );

        $this->eventBus->attach(
            MessageBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$finalizeIsTriggered) {
                $finalizeIsTriggered = true;
                $actionEvent->setParam(MessageBus::EVENT_PARAM_EXCEPTION, null);
            },
            1000
        );

        $customMessage = new CustomMessage('foo');

        $this->eventBus->dispatch($customMessage);

        $this->assertTrue($finalizeIsTriggered);
        $this->assertSame($customMessage, $handler->getLastMessage());
        $this->assertEquals(1, $handler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_collects_exceptions_if_mode_is_enabled(): void
    {
        $handler = new MessageHandler();
        $errorProducer = new AutoErrorProducer();
        $finalizeIsTriggered = false;
        $listenerExceptions = [];

        $this->eventBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $e) use ($handler, $errorProducer): void {
                if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                    $e->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [$handler, $errorProducer, $handler]);
                }
            },
            MessageBus::PRIORITY_ROUTE
        );

        $this->eventBus->attach(
            MessageBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$finalizeIsTriggered, &$listenerExceptions) {
                $finalizeIsTriggered = true;
                if ($exception = $actionEvent->getParam(MessageBus::EVENT_PARAM_EXCEPTION)) {
                    if ($exception instanceof EventListenerException) {
                        $listenerExceptions = $exception->listenerExceptions();
                    }
                }
                $actionEvent->setParam(MessageBus::EVENT_PARAM_EXCEPTION, null);
            },
            1000
        );

        $this->eventBus->enableCollectExceptions();

        $customMessage = new CustomMessage('foo');

        $this->eventBus->dispatch($customMessage);

        $this->assertTrue($finalizeIsTriggered);
        $this->assertCount(1, $listenerExceptions);
        $this->assertInstanceOf(\Throwable::class, $listenerExceptions[0]);
        $this->assertSame($customMessage, $handler->getLastMessage());
        $this->assertEquals(2, $handler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_always_triggers_finalize_listeners_regardless_whether_the_propagation_of_the_event_has_been_stopped(): void
    {
        $this->eventBus->attach(EventBus::EVENT_DISPATCH, function (ActionEvent $event) {
            $event->setParam(EventBus::EVENT_PARAM_MESSAGE_HANDLER, function (): void {
            });
        }, EventBus::PRIORITY_LOCATE_HANDLER + 1);
        $this->eventBus->attach(EventBus::EVENT_DISPATCH, function (ActionEvent $event): void {
            $event->stopPropagation();
        }, EventBus::PRIORITY_INVOKE_HANDLER - 1);

        $this->eventBus->attach(MessageBus::EVENT_FINALIZE, function (): void {
        }, 3);
        $finalizeHasBeenCalled = false;
        $this->eventBus->attach(MessageBus::EVENT_FINALIZE, function () use (&$finalizeHasBeenCalled): void {
            $finalizeHasBeenCalled = true;
        }, 2);

        try {
            $this->eventBus->dispatch('a message');
        } catch (\Throwable $e) {
            // ignore
        }

        $this->assertTrue($finalizeHasBeenCalled);
    }
}
