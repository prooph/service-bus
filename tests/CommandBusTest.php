<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2019 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Exception\CommandDispatchException;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\DoSomething;
use ProophTest\ServiceBus\Mock\ErrorProducer;
use ProophTest\ServiceBus\Mock\MessageHandler;

class CommandBusTest extends TestCase
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    protected function setUp()
    {
        $this->commandBus = new CommandBus();
    }

    /**
     * @test
     */
    public function it_dispatches_a_message_using_the_default_process(): void
    {
        $doSomething = new DoSomething(['todo' => 'buy milk']);

        $receivedMessage = null;
        $dispatchEvent = null;
        $this->commandBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$receivedMessage, &$dispatchEvent): void {
                $actionEvent->setParam(
                    MessageBus::EVENT_PARAM_MESSAGE_HANDLER,
                    function (DoSomething $doSomething) use (&$receivedMessage): void {
                        $receivedMessage = $doSomething;
                    }
                );

                $dispatchEvent = $actionEvent;
            },
            MessageBus::PRIORITY_ROUTE
        );

        $this->commandBus->dispatch($doSomething);

        $this->assertSame($doSomething, $receivedMessage);
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
        $this->commandBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$initializeIsTriggered): void {
                $initializeIsTriggered = true;
            },
            MessageBus::PRIORITY_INITIALIZE
        );

        //Should be triggered because we dispatch a message that does not
        //implement Prooph\Common\Messaging\HasMessageName
        $this->commandBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$detectMessageNameIsTriggered): void {
                $detectMessageNameIsTriggered = true;
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, 'custom-message');
            },
            MessageBus::PRIORITY_DETECT_MESSAGE_NAME
        );

        //Should be triggered because we did not provide a message-handler yet
        $this->commandBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use (&$routeIsTriggered): void {
                $routeIsTriggered = true;
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === 'custom-message') {
                    //We provide the message handler as a string (service id) to tell the bus to trigger the locate-handler event
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, 'error-producer');
                }
            },
            MessageBus::PRIORITY_ROUTE
        );

        //Should be triggered because we provided the message-handler as string (service id)
        $this->commandBus->attach(
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
        $this->commandBus->attach(
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
        $this->commandBus->attach(
            MessageBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$finalizeIsTriggered): void {
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_EXCEPTION) instanceof \Exception) {
                    $finalizeIsTriggered = true;
                }
            },
            100 // before exception is thrown
        );

        $customMessage = new CustomMessage('I have no further meaning');

        try {
            $this->commandBus->dispatch($customMessage);
        } catch (CommandDispatchException $exception) {
            $this->assertNotNull($exception->getPrevious());
            $this->assertEquals('I can only throw exceptions', $exception->getPrevious()->getMessage());
        }

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

        $this->commandBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $e) use ($handler): void {
                if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                    $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);
                }
            },
            MessageBus::PRIORITY_ROUTE
        );

        $customMessage = new CustomMessage('foo');

        $this->commandBus->dispatch($customMessage);

        $this->assertSame($customMessage, $handler->getLastMessage());
    }

    /**
     * @test
     */
    public function it_throws_service_bus_exception_if_exception_is_not_handled_by_a_plugin(): void
    {
        $this->expectException(MessageDispatchException::class);

        $this->commandBus->attach(
            MessageBus::EVENT_DISPATCH,
            function () {
                throw new \Exception('ka boom');
            },
            MessageBus::PRIORITY_INITIALIZE
        );

        $this->commandBus->dispatch('throw it');
    }

    /**
     * @test
     */
    public function it_throws_exception_if_event_has_no_handler_after_it_has_been_set_and_event_was_triggered(): void
    {
        $this->expectException(MessageDispatchException::class);

        $this->commandBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $e): void {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, null);
            },
            MessageBus::PRIORITY_INITIALIZE
        );

        $this->commandBus->dispatch('throw it');
    }

    /**
     * @test
     */
    public function it_throws_exception_if_message_was_not_handled(): void
    {
        $this->expectException(MessageDispatchException::class);

        $this->commandBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $e): void {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, new \stdClass());
            },
            MessageBus::PRIORITY_INITIALIZE
        );

        $this->commandBus->dispatch('throw it');
    }

    /**
     * @test
     */
    public function it_queues_new_commands_as_long_as_it_is_dispatching(): void
    {
        $messageHandler = new MessageHandler();

        (new CommandRouter())
            ->route(CustomMessage::class)->to($messageHandler)
            ->route('initial message')->to(function () use ($messageHandler): void {
                $delayedMessage = new CustomMessage('delayed message');

                $this->commandBus->dispatch($delayedMessage);

                $this->assertEquals(0, $messageHandler->getInvokeCounter());
            })
            ->attachToMessageBus($this->commandBus);

        $this->commandBus->dispatch('initial message');

        $this->assertEquals(1, $messageHandler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_passes_queued_commands_to_command_dispatch_exception_in_case_of_an_error(): void
    {
        $messageHandler = new MessageHandler();

        (new CommandRouter())
            ->route(CustomMessage::class)->to($messageHandler)
            ->route('initial message')->to(function () use ($messageHandler): void {
                $delayedMessage = new CustomMessage('delayed message');

                $this->commandBus->dispatch($delayedMessage);

                throw new \Exception('Ka Boom');
            })
            ->attachToMessageBus($this->commandBus);

        $commandDispatchException = null;

        try {
            $this->commandBus->dispatch('initial message');
        } catch (CommandDispatchException $ex) {
            $commandDispatchException = $ex;
        }

        $this->assertInstanceOf(CommandDispatchException::class, $commandDispatchException);
        $this->assertSame(1, \count($commandDispatchException->getPendingCommands()));
        $this->assertSame(CustomMessage::class, \get_class($commandDispatchException->getPendingCommands()[0]));
    }

    /**
     * @test
     */
    public function it_always_triggers_finalize_listeners_regardless_whether_the_propagation_of_the_event_has_been_stopped(): void
    {
        $this->commandBus->attach(CommandBus::EVENT_DISPATCH, function (ActionEvent $event) {
            $event->setParam(CommandBus::EVENT_PARAM_MESSAGE_HANDLER, function (): void {
            });
        }, CommandBus::PRIORITY_LOCATE_HANDLER + 1);
        $this->commandBus->attach(CommandBus::EVENT_DISPATCH, function (ActionEvent $event): void {
            $event->stopPropagation();
        }, CommandBus::PRIORITY_INVOKE_HANDLER - 1);

        $this->commandBus->attach(MessageBus::EVENT_FINALIZE, function (): void {
        }, 3);
        $finalizeHasBeenCalled = false;
        $this->commandBus->attach(MessageBus::EVENT_FINALIZE, function () use (&$finalizeHasBeenCalled): void {
            $finalizeHasBeenCalled = true;
        }, 2);

        try {
            $this->commandBus->dispatch('a message');
        } catch (\Throwable $e) {
            // ignore
        }

        $this->assertTrue($finalizeHasBeenCalled);
    }
}
