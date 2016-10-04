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

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\Common\Event\ListenerHandler;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\FinalizeGuard;
use Prooph\ServiceBus\QueryBus;
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
    public function it_attaches_to_action_event_emitter() : void
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
    public function it_allows_when_authorization_service_grants_access_without_deferred() : void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(true);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(QueryBus::EVENT_PARAM_PROMISE)->willReturn(null);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $this->assertNull($routeGuard->onFinalize($actionEvent->reveal()));
    }

    /**
     * @test
     */
    public function it_allows_when_authorization_service_grants_access_with_deferred() : void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', 'result')->willReturn(true);

        $deferred = new Deferred();
        $deferred->resolve('result');

        $actionEvent = new DefaultActionEvent(QueryBus::EVENT_FINALIZE);
        $actionEvent->setParam(QueryBus::EVENT_PARAM_PROMISE, $deferred->promise());
        $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_NAME, 'test_event');

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $routeGuard->onFinalize($actionEvent);

        $actionEvent->getParam(QueryBus::EVENT_PARAM_PROMISE)->done();
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Plugin\Guard\UnauthorizedException
     * @expectedExceptionMessage You are not authorized to access this resource
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_without_deferred() : void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(false);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(QueryBus::EVENT_PARAM_PROMISE)->willReturn(null);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');
        $actionEvent->stopPropagation(true)->shouldBeCalled();

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $routeGuard->onFinalize($actionEvent->reveal());
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Plugin\Guard\UnauthorizedException
     * @expectedExceptionMessage You are not authorized to access this resource
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_with_deferred() : void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', 'result')->willReturn(false);

        $deferred = new Deferred();
        $deferred->resolve('result');

        $actionEvent = new DefaultActionEvent(QueryBus::EVENT_FINALIZE);
        $actionEvent->setParam(QueryBus::EVENT_PARAM_PROMISE, $deferred->promise());
        $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_NAME, 'test_event');

        $routeGuard = new FinalizeGuard($authorizationService->reveal());

        $routeGuard->onFinalize($actionEvent);

        $this->assertTrue($actionEvent->propagationIsStopped());
        $actionEvent->getParam(QueryBus::EVENT_PARAM_PROMISE)->done();
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Plugin\Guard\UnauthorizedException
     * @expectedExceptionMessage You are not authorized to access the resource "test_event"
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_without_deferred_and_exposes_message_name() : void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(false);

        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(QueryBus::EVENT_PARAM_PROMISE)->willReturn(null);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)->willReturn('test_event');
        $actionEvent->stopPropagation(true)->shouldBeCalled();

        $routeGuard = new FinalizeGuard($authorizationService->reveal(), true);

        $routeGuard->onFinalize($actionEvent->reveal());
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Plugin\Guard\UnauthorizedException
     * @expectedExceptionMessage You are not authorized to access the resource "test_event"
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_with_deferred_and_exposes_message_name() : void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', 'result')->willReturn(false);

        $deferred = new Deferred();
        $deferred->resolve('result');

        $actionEvent = new DefaultActionEvent(QueryBus::EVENT_FINALIZE);
        $actionEvent->setParam(QueryBus::EVENT_PARAM_PROMISE, $deferred->promise());
        $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_NAME, 'test_event');

        $routeGuard = new FinalizeGuard($authorizationService->reveal(), true);

        $routeGuard->onFinalize($actionEvent);

        $this->assertTrue($actionEvent->propagationIsStopped());
        $actionEvent->getParam(QueryBus::EVENT_PARAM_PROMISE)->done();
    }
}
