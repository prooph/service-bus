<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 8/2/15 - 8:17 PM
 */
namespace Prooph\ServiceBusTest;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\Exception\ServiceBusException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\InvokeStrategy\FinderInvokeStrategy;
use Prooph\ServiceBus\QueryBus;
use Prooph\ServiceBusTest\Mock\CustomMessage;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\ErrorProducer;
use Prooph\ServiceBusTest\Mock\FetchSomething;
use Prooph\ServiceBusTest\Mock\Finder;
use Prooph\ServiceBusTest\Mock\MessageHandler;
use React\Promise\Deferred;
use React\Promise\Promise;

final class QueryBusTest extends TestCase
{
    /**
     * @var QueryBus
     */
    private $queryBus;

    protected function setUp()
    {
        $this->queryBus = new QueryBus();
    }
    /**
     * @test
     */
    function it_dispatches_a_message_using_the_default_process()
    {
        $fetchSomething = new FetchSomething(['filter' => 'todo']);

        $receivedMessage = null;

        $this->queryBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $actionEvent) use (&$receivedMessage) {
            $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, function (FetchSomething $fetchSomething, Deferred $deferred) use (&$receivedMessage) {
                $deferred->resolve($fetchSomething);
            });
        });

        $promise = $this->queryBus->dispatch($fetchSomething);

        $promise->then(function($result) use (&$receivedMessage) {
            $receivedMessage = $result;
        });

        $this->assertSame($fetchSomething, $receivedMessage);
    }

    /**
     * @test
     */
    function it_triggers_all_defined_action_events()
    {
        $initializeIsTriggered = false;
        $detectMessageNameIsTriggered = false;
        $routeIsTriggered = false;
        $locateHandlerIsTriggered = false;
        $invokeFinderIsTriggered = false;
        $handleErrorIsTriggered = false;
        $finalizeIsTriggered = false;

        //Should always be triggered
        $this->queryBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INITIALIZE,
            function (ActionEvent $actionEvent) use (&$initializeIsTriggered) {
                $initializeIsTriggered = true;
            }
        );

        //Should be triggered because we dispatch a message that does not
        //implement Prooph\Common\Messaging\HasMessageName
        $this->queryBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_DETECT_MESSAGE_NAME,
            function (ActionEvent $actionEvent) use (&$detectMessageNameIsTriggered) {
                $detectMessageNameIsTriggered = true;
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, "custom-message");
            }
        );

        //Should be triggered because we did not provide a message-handler yet
        $this->queryBus->getActionEventEmitter()->attachListener(
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
        $this->queryBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_LOCATE_HANDLER,
            function (ActionEvent $actionEvent) use (&$locateHandlerIsTriggered) {
                $locateHandlerIsTriggered = true;
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER) === "error-producer") {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, new ErrorProducer());
                }
            }
        );

        //Should be triggered because the message-handler is not callable
        $this->queryBus->getActionEventEmitter()->attachListener(
            QueryBus::EVENT_INVOKE_FINDER,
            function (ActionEvent $actionEvent) use (&$invokeFinderIsTriggered) {
                $invokeFinderIsTriggered = true;
                $handler = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);
                if ($handler instanceof ErrorProducer) {
                    $handler->throwException($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE));
                }
            }
        );

        //Should be triggered because the message-handler threw an exception
        $this->queryBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_HANDLE_ERROR,
            function (ActionEvent $actionEvent) use (&$handleErrorIsTriggered) {
                $handleErrorIsTriggered = true;

                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_EXCEPTION) instanceof \Exception) {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_EXCEPTION, null);
                }
            }
        );

        //Should always be triggered
        $this->queryBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$finalizeIsTriggered) {
                $finalizeIsTriggered = true;
            }
        );

        $customMessage = new CustomMessage("I have no further meaning");

        $this->queryBus->dispatch($customMessage);

        $this->assertTrue($initializeIsTriggered);
        $this->assertTrue($detectMessageNameIsTriggered);
        $this->assertTrue($routeIsTriggered);
        $this->assertTrue($locateHandlerIsTriggered);
        $this->assertTrue($invokeFinderIsTriggered);
        $this->assertTrue($handleErrorIsTriggered);
        $this->assertTrue($finalizeIsTriggered);
    }

    /**
     * @test
     */
    function it_uses_the_fqcn_of_the_message_if_message_name_was_not_provided_and_message_does_not_implement_has_message_name()
    {
        $handler = new Finder();

        $this->queryBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $e) use ($handler) {
            if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);
            }
        });

        $this->queryBus->utilize(new FinderInvokeStrategy());

        $customMessage = new CustomMessage("foo");

        $promise = $this->queryBus->dispatch($customMessage);

        $this->assertSame($customMessage, $handler->getLastMessage());
        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertInstanceOf(Deferred::class, $handler->getLastDeferred());
    }

    /**
     * @test
     */
    function it_rejects_the_deferred_with_a_service_bus_exception_if_exception_is_not_handled_by_a_plugin()
    {
        $exception = null;

        $this->queryBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_INITIALIZE, function () {
            throw new \Exception("ka boom");
        });

        $promise = $this->queryBus->dispatch("throw it");

        $promise->otherwise(function ($ex) use (&$exception) {
            $exception = $ex;
        });

        $this->assertInstanceOf(ServiceBusException::class, $exception);
    }
} 