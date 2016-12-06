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

namespace ProophTest\ServiceBus\Plugin\Guard;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ListenerHandler;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\RouteGuard;
use Prooph\ServiceBus\Plugin\Guard\UnauthorizedException;

class RouteGuardTest extends TestCase
{
    /**
     * @test
     */
    public function it_attaches_to_action_event_emitter(): void
    {
        $listenerHandler = $this->prophesize(ListenerHandler::class);

        $authorizationService = $this->prophesize(AuthorizationService::class);

        $routeGuard = new RouteGuard($authorizationService->reveal());

        $actionEventEmitter = $this->prophesize(ActionEventEmitter::class);
        $actionEventEmitter
            ->attachListener(MessageBus::EVENT_DISPATCH, [$routeGuard, 'onRoute'], MessageBus::PRIORITY_ROUTE)
            ->willReturn($listenerHandler->reveal());

        $routeGuard->attach($actionEventEmitter->reveal());
    }

    /**
     * @test
     */
    public function it_allows_when_authorization_service_grants_access(): void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', new \stdClass())->willReturn(true);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE)->willReturn(new \stdClass());

        $routeGuard = new RouteGuard($authorizationService->reveal());

        $this->assertNull($routeGuard->onRoute($actionEvent->reveal()));
    }

    /**
     * @test
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('You are not authorized to access this resource');

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', new \stdClass())->willReturn(false);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE)->willReturn(new \stdClass());
        $actionEvent->stopPropagation(true)->shouldBeCalled();

        $routeGuard = new RouteGuard($authorizationService->reveal());

        $routeGuard->onRoute($actionEvent->reveal());
    }

    /**
     * @test
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_and_exposed_message_name(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('You are not authorized to access the resource "test_event"');

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', new \stdClass())->willReturn(false);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE)->willReturn(new \stdClass());
        $actionEvent->stopPropagation(true)->shouldBeCalled();

        $routeGuard = new RouteGuard($authorizationService->reveal(), true);

        $routeGuard->onRoute($actionEvent->reveal());
    }
}
