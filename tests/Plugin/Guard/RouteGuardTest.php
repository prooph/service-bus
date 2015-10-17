<?php

namespace ProophTest\ServiceBus\Plugin\Guard;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ListenerHandler;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\RouteGuard;

/**
 * Class RouteGuardTest
 * @package ProophTest\ServiceBus\Plugin\Guard
 */
final class RouteGuardTest extends TestCase
{
    /**
     * @test
     */
    public function it_attaches_to_action_event_emitter()
    {
        $listenerHandler = $this->prophesize(ListenerHandler::class);

        $authorizationService = $this->prophesize(AuthorizationService::class);

        $routeGuard = new RouteGuard($authorizationService->reveal());

        $actionEventEmitter = $this->prophesize(ActionEventEmitter::class);
        $actionEventEmitter
            ->attachListener(MessageBus::EVENT_ROUTE, [$routeGuard, 'onRoute'], 1000)
            ->willReturn($listenerHandler->reveal());

        $routeGuard->attach($actionEventEmitter->reveal());
    }

    /**
     * @test
     */
    public function it_allows_when_authorization_service_grants_access()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(true);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');

        $routeGuard = new RouteGuard($authorizationService->reveal());

        $this->assertNull($routeGuard->onRoute($actionEvent->reveal()));
    }

    /**
     * @test
     * @expectedException Prooph\ServiceBus\Plugin\Guard\UnauthorizedException
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(false);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');
        $actionEvent->stopPropagation(true)->willReturn(null);

        $routeGuard = new RouteGuard($authorizationService->reveal());

        $routeGuard->onRoute($actionEvent->reveal());
    }
}
