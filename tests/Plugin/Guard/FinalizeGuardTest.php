<?php

namespace ProophTest\ServiceBus\Plugin\Guard;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ListenerHandler;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\FinalizeGuard;
use React\Promise\Deferred;

/**
 * Class FinalizeGuardTest
 * @package ProophTest\ServiceBus\Plugin\Guard
 */
final class FinalizeGuardTest extends TestCase
{
    /**
     * @test
     */
    public function it_attaches_to_action_event_emitter()
    {
        $listenerHandler = $this->prophesize(ListenerHandler::class);

        $authorizationService = $this->prophesize(AuthorizationService::class);

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $actionEventEmitter = $this->prophesize(ActionEventEmitter::class);
        $actionEventEmitter
            ->attachListener(MessageBus::EVENT_FINALIZE, [$routeGuard, 'onFinalize'], -1000)
            ->willReturn($listenerHandler->reveal());

        $routeGuard->attach($actionEventEmitter->reveal());
    }

    /**
     * @test
     */
    public function it_allows_when_authorization_service_grants_access_without_deferred()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(true);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam('query-deferred')->willReturn(null);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $this->assertNull($routeGuard->onFinalize($actionEvent->reveal()));
    }

    /**
     * @test
     */
    public function it_allows_when_authorization_service_grants_access_with_deferred()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', 'result')->willReturn(true);

        $deferred = new Deferred();
        $deferred->resolve('result');

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam('query-deferred')->willReturn($deferred);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $this->assertNull($routeGuard->onFinalize($actionEvent->reveal()));
    }

    /**
     * @test
     * @expectedException Prooph\ServiceBus\Plugin\Guard\UnauthorizedException
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_without_deferred()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(false);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam('query-deferred')->willReturn(null);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');
        $actionEvent->stopPropagation(true)->willReturn(null);

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $routeGuard->onFinalize($actionEvent->reveal());
    }

    /**
     * @test
     * @expectedException Prooph\ServiceBus\Plugin\Guard\UnauthorizedException
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_with_deferred()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', 'result')->willReturn(false);

        $deferred = new Deferred();
        $deferred->resolve('result');

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam('query-deferred')->willReturn($deferred);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');
        $actionEvent->stopPropagation(true)->willReturn(null);

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $routeGuard->onFinalize($actionEvent->reveal());
    }
}
