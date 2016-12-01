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

namespace ProophTest\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\MessageBus;
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
        $this->eventBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $actionEvent) use (&$receivedMessage, &$dispatchEvent) {
            $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [function (SomethingDone $somethingDone) use (&$receivedMessage) {
                $receivedMessage = $somethingDone;
            }]);

            $dispatchEvent = $actionEvent;
        });

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
        $handleErrorIsTriggered = false;
        $finalizeIsTriggered = false;

        //Should always be triggered
        $this->eventBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INITIALIZE,
            function (ActionEvent $actionEvent) use (&$initializeIsTriggered) {
                $initializeIsTriggered = true;
            }
        );

        //Should be triggered because we dispatch a message that does not
        //implement Prooph\Common\Messaging\HasMessageName
        $this->eventBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_DETECT_MESSAGE_NAME,
            function (ActionEvent $actionEvent) use (&$detectMessageNameIsTriggered) {
                $detectMessageNameIsTriggered = true;
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, 'custom-message');
            }
        );

        //Should be triggered because we did not provide a message-handler yet
        $this->eventBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_ROUTE,
            function (ActionEvent $actionEvent) use (&$routeIsTriggered) {
                $routeIsTriggered = true;
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === 'custom-message') {
                    //We provide the message handler as a string (service id) to tell the bus to trigger the locate-handler event
                    $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, ['error-producer']);
                }
            }
        );

        //Should be triggered because we provided the message-handler as string (service id)
        $this->eventBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_LOCATE_HANDLER,
            function (ActionEvent $actionEvent) use (&$locateHandlerIsTriggered) {
                $locateHandlerIsTriggered = true;
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER) === 'error-producer') {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, new ErrorProducer());
                }
            }
        );

        //Should be triggered because the message-handler is not callable
        $this->eventBus->getActionEventEmitter()->attachListener(
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
        $this->eventBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_HANDLE_ERROR,
            function (ActionEvent $actionEvent) use (&$handleErrorIsTriggered) {
                $handleErrorIsTriggered = true;

                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_EXCEPTION) instanceof \Exception) {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_EXCEPTION, null);
                }
            }
        );

        //Should always be triggered
        $this->eventBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$finalizeIsTriggered) {
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
        $this->assertTrue($handleErrorIsTriggered);
        $this->assertTrue($finalizeIsTriggered);
    }

    /**
     * @test
     */
    public function it_uses_the_fqcn_of_the_message_if_message_name_was_not_provided_and_message_does_not_implement_has_message_name(): void
    {
        $handler = new MessageHandler();

        $this->eventBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $e) use ($handler) {
            if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                $e->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [$handler]);
            }
        });

        $customMessage = new CustomMessage('foo');

        $this->eventBus->dispatch($customMessage);

        $this->assertSame($customMessage, $handler->getLastMessage());
    }

    /**
     * @test
     * @expectedException Prooph\ServiceBus\Exception\MessageDispatchException
     */
    public function it_throws_service_bus_exception_if_exception_is_not_handled_by_a_plugin(): void
    {
        try {
            $this->eventBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_INITIALIZE, function () {
                throw new \Exception('ka boom');
            });

            $this->eventBus->dispatch('throw it');
        } catch (MessageDispatchException $e) {
            $this->assertInstanceOf(DefaultActionEvent::class, $e->getFailedDispatchEvent());

            throw $e;
        }
    }

    /**
     * @test
     */
    public function it_invokes_all_listeners(): void
    {
        $handler = new MessageHandler();

        $this->eventBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $e) use ($handler) {
            if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                $e->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [$handler, $handler]);
            }
        });

        $customMessage = new CustomMessage('foo');

        $this->eventBus->dispatch($customMessage);

        $this->assertSame($customMessage, $handler->getLastMessage());
        $this->assertEquals(2, $handler->getInvokeCounter());
    }
}
