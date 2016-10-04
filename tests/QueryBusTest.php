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
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\InvokeStrategy\FinderInvokeStrategy;
use Prooph\ServiceBus\QueryBus;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\ErrorProducer;
use ProophTest\ServiceBus\Mock\FetchSomething;
use ProophTest\ServiceBus\Mock\Finder;
use React\Promise\Deferred;
use React\Promise\Promise;

/**
 * Class QueryBusTest
 * @package ProophTest\ServiceBus
 */
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
    public function it_dispatches_a_message_using_the_default_process() : void
    {
        $fetchSomething = new FetchSomething(['filter' => 'todo']);

        $receivedMessage = null;
        $dispatchEvent = null;
        $this->queryBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $actionEvent) use (&$receivedMessage, &$dispatchEvent) {
            $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, function (FetchSomething $fetchSomething, Deferred $deferred) use (&$receivedMessage) {
                $deferred->resolve($fetchSomething);
            });
            $dispatchEvent = $actionEvent;
        });

        $promise = $this->queryBus->dispatch($fetchSomething);

        $promise->then(function ($result) use (&$receivedMessage) {
            $receivedMessage = $result;
        });

        $this->assertSame($fetchSomething, $receivedMessage);
        $this->assertTrue($dispatchEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED));
    }

    /**
     * @test
     */
    public function it_triggers_all_defined_action_events() : void
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
    public function it_uses_the_fqcn_of_the_message_if_message_name_was_not_provided_and_message_does_not_implement_has_message_name() : void
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
    public function it_rejects_the_deferred_with_a_service_bus_exception_if_exception_is_not_handled_by_a_plugin() : void
    {
        $exception = null;

        $this->queryBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_INITIALIZE, function () {
            throw new \Exception("ka boom");
        });

        $promise = $this->queryBus->dispatch("throw it");

        $promise->otherwise(function ($ex) use (&$exception) {
            $exception = $ex;
        });

        $this->assertInstanceOf(MessageDispatchException::class, $exception);
        $this->assertInstanceOf(DefaultActionEvent::class, $exception->getFailedDispatchEvent());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_event_has_no_handler_after_it_has_been_set_and_event_was_triggered() : void
    {
        $exception = null;

        $this->queryBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INITIALIZE, function (ActionEvent $e) {
                $e->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLER, null);
            }
        );

        $promise = $this->queryBus->dispatch("throw it");

        $promise->otherwise(function ($ex) use (&$exception) {
            $exception = $ex;
        });

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Message dispatch failed during route phase. Error: QueryBus was not able to identify a Finder for query throw it', $exception->getMessage());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_event_has_stopped_propagation() : void
    {
        $exception = null;

        $this->queryBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INITIALIZE, function (ActionEvent $e) {
                $e->stopPropagation(true);
            }
        );

        $promise = $this->queryBus->dispatch("throw it");

        $promise->otherwise(function ($ex) use (&$exception) {
            $exception = $ex;
        });

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Message dispatch failed during initialize phase. Error: Dispatch has stopped unexpectedly.', $exception->getMessage());
    }

    /**
     * @test
     */
    public function it_can_deactive_an_action_event_listener_aggregate() : void
    {
        $handler = new Finder();

        $this->queryBus->getActionEventEmitter()->attachListener(MessageBus::EVENT_ROUTE, function (ActionEvent $e) use ($handler) {
            if ($e->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME) === CustomMessage::class) {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);
            }
        });

        $plugin = new FinderInvokeStrategy();
        $this->queryBus->utilize($plugin);
        $this->queryBus->deactivate($plugin);

        $customMessage = new CustomMessage("foo");

        $promise = $this->queryBus->dispatch($customMessage);

        $this->assertNull($handler->getLastMessage());
        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertNull($handler->getLastDeferred());
    }

    /**
     * @test
     * @expectedException Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_exception_if_message_was_not_handled() : void
    {
        $this->queryBus->getActionEventEmitter()->attachListener(
            MessageBus::EVENT_INITIALIZE,
            function (ActionEvent $e) {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, new \stdClass());
            }
        );

        $promise = $this->queryBus->dispatch('throw it');

        $promise->done();
    }
}
