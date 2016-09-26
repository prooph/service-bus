<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace ProophTest\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Exception\CommandDispatchException;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\DoSomething;
use ProophTest\ServiceBus\Mock\ErrorProducer;
use ProophTest\ServiceBus\Mock\MessageHandler;

/**
 * Class CommandBusTest
 * @package ProophTest\ServiceBus
 */
final class CommandBusTest extends TestCase
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
    public function it_dispatches_a_message_using_the_default_process()
    {
        $doSomething = new DoSomething(['todo' => 'buy milk']);

        $receivedMessage = null;
        $dispatchEvent = null;
        $this->commandBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $actionEvent) use (&$receivedMessage, &$dispatchEvent) {
            $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, function (DoSomething $doSomething) use (&$receivedMessage) {
                $receivedMessage = $doSomething;
            });

            $dispatchEvent = $actionEvent;
        });

        $this->commandBus->dispatch($doSomething);

        $this->assertSame($doSomething, $receivedMessage);
        $this->assertTrue($dispatchEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED));
    }

    /**
     * @test
     */
    public function it_triggers_all_defined_action_events()
    {
        $initializeIsTriggered = false;
        $detectMessageNameIsTriggered = false;
        $routeIsTriggered = false;
        $locateHandlerIsTriggered = false;
        $invokeHandlerIsTriggered = false;
        $handleErrorIsTriggered = false;
        $finalizeIsTriggered = false;

        //Should always be triggered
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INITIALIZE,
            function (ActionEvent $actionEvent) use (&$initializeIsTriggered) {
                $initializeIsTriggered = true;
            }
        );

        //Should be triggered because we dispatch a message that does not
        //implement Prooph\Common\Messaging\HasMessageName
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_DETECT_MESSAGE_NAME,
            function (ActionEvent $actionEvent) use (&$detectMessageNameIsTriggered) {
                $detectMessageNameIsTriggered = true;
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, "custom-message");
            }
        );

        //Should be triggered because we did not provide a message-handler yet
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_ROUTE,
            function (ActionEvent $actionEvent) use (&$routeIsTriggered) {
                $routeIsTriggered = true;
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === "custom-message") {
                    //We provide the message handler as a string (service id) to tell the bus to trigger the locate-handler event
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, "error-producer");
                }
            }
        );

        //Should be triggered because we provided the message-handler as string (service id)
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_LOCATE_HANDLER,
            function (ActionEvent $actionEvent) use (&$locateHandlerIsTriggered) {
                $locateHandlerIsTriggered = true;
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER) === "error-producer") {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, new ErrorProducer());
                }
            }
        );

        //Should be triggered because the message-handler is not callable
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INVOKE_HANDLER,
            function (ActionEvent $actionEvent) use (&$invokeHandlerIsTriggered) {
                $invokeHandlerIsTriggered = true;
                $handler = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);
                if ($handler instanceof ErrorProducer) {
                    $handler->throwException($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE));
                }
            }
        );

        //Should be triggered because the message-handler threw an exception
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_HANDLE_ERROR,
            function (ActionEvent $actionEvent) use (&$handleErrorIsTriggered) {
                $handleErrorIsTriggered = true;

                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_EXCEPTION) instanceof \Exception) {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_EXCEPTION, null);
                }
            }
        );

        //Should always be triggered
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$finalizeIsTriggered) {
                $finalizeIsTriggered = true;
            }
        );

        $customMessage = new CustomMessage("I have no further meaning");

        $this->commandBus->dispatch($customMessage);

        $this->assertTrue($initializeIsTriggered);
        $this->assertTrue($detectMessageNameIsTriggered);
        $this->assertTrue($routeIsTriggered);
        $this->assertTrue($locateHandlerIsTriggered);
        $this->assertTrue($invokeHandlerIsTriggered);
        $this->assertTrue($handleErrorIsTriggered);
        $this->assertTrue($finalizeIsTriggered);
    }

    /**
     * @test
     */
    public function it_uses_the_fqcn_of_the_message_if_message_name_was_not_provided_and_message_does_not_implement_has_message_name()
    {
        $handler = new MessageHandler();

        $this->commandBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $e) use ($handler) {
            if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);
            }
        });

        $customMessage = new CustomMessage("foo");

        $this->commandBus->dispatch($customMessage);

        $this->assertSame($customMessage, $handler->getLastMessage());
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\MessageDispatchException
     */
    public function it_throws_service_bus_exception_if_exception_is_not_handled_by_a_plugin()
    {
        try {
            $this->commandBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_INITIALIZE, function () {
                throw new \Exception("ka boom");
            });

            $this->commandBus->dispatch("throw it");
        } catch (MessageDispatchException $e) {
            $this->assertInstanceOf(DefaultActionEvent::class, $e->getFailedDispatchEvent());

            throw $e;
        }
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_exception_if_event_has_no_handler_after_it_has_been_set_and_event_was_triggered()
    {
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INITIALIZE, function (ActionEvent $e) {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, null);
            }
        );

        $this->commandBus->dispatch("throw it");
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_exception_if_message_was_not_handled()
    {
        $this->commandBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INITIALIZE,
            function (ActionEvent $e) {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, new \stdClass());
            }
        );

        $this->commandBus->dispatch('throw it');
    }

    /**
     * @test
     */
    public function it_queues_new_commands_as_long_as_it_is_dispatching()
    {
        $messageHandler = new MessageHandler();

        $this->commandBus->utilize(
            (new CommandRouter())
                ->route(CustomMessage::class)->to($messageHandler)
                ->route('initial message')->to(function () use ($messageHandler) {
                    $delayedMessage = new CustomMessage("delayed message");

                    $this->commandBus->dispatch($delayedMessage);

                    $this->assertEquals(0, $messageHandler->getInvokeCounter());
                })
        );

        $this->commandBus->dispatch('initial message');

        $this->assertEquals(1, $messageHandler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_passes_queued_commands_to_command_dispatch_exception_in_case_of_an_error()
    {
        $messageHandler = new MessageHandler();

        $this->commandBus->utilize(
            (new CommandRouter())
                ->route(CustomMessage::class)->to($messageHandler)
                ->route('initial message')->to(function () use ($messageHandler) {
                    $delayedMessage = new CustomMessage("delayed message");

                    $this->commandBus->dispatch($delayedMessage);

                    throw new \Exception("Ka Boom");
                })
        );

        $commandDispatchException = null;

        try {
            $this->commandBus->dispatch('initial message');
        } catch (CommandDispatchException $ex) {
            $commandDispatchException = $ex;
        }

        $this->assertInstanceOf(CommandDispatchException::class, $commandDispatchException);
        $this->assertSame(1, count($commandDispatchException->getPendingCommands()));
        $this->assertSame(CustomMessage::class, get_class($commandDispatchException->getPendingCommands()[0]));
    }
}
