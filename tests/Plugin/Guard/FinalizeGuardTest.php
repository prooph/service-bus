<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\Guard;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\FinalizeGuard;
use Prooph\ServiceBus\Plugin\Guard\UnauthorizedException;
use Prooph\ServiceBus\QueryBus;

class FinalizeGuardTest extends TestCase
{
    /**
     * @var MessageBus
     */
    protected $messageBus;

    protected function setUp(): void
    {
        $this->messageBus = new QueryBus();
    }

    /**
     * @test
     */
    public function it_allows_when_authorization_service_grants_access_without_deferred(): void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(true)->shouldBeCalled();

        $messageBus = new EventBus();

        $routeGuard = new FinalizeGuard($authorizationService->reveal());
        $routeGuard->attachToMessageBus($messageBus);

        $messageBus->dispatch('test_event');
    }

    /**
     * @test
     */
    public function it_allows_when_authorization_service_grants_access_with_deferred(): void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', 'result')->willReturn(true)->shouldBeCalled();

        $routeGuard = new FinalizeGuard($authorizationService->reveal());
        $routeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $deferred = $actionEvent->getParam(QueryBus::EVENT_PARAM_DEFERRED);
                $deferred->resolve('result');
                $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLED, true);
            },
            QueryBus::PRIORITY_LOCATE_HANDLER + 1000
        );

        $promise = $this->messageBus->dispatch('test_event');
        $promise->done();
    }

    /**
     * @test
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_without_deferred(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('You are not authorized to access this resource');

        $this->messageBus = new EventBus();

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(false);

        $routeGuard = new FinalizeGuard($authorizationService->reveal());
        $routeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLED, true);
            },
            QueryBus::PRIORITY_LOCATE_HANDLER + 1000
        );

        try {
            $promise = $this->messageBus->dispatch('test_event');
            $promise->done();
        } catch (MessageDispatchException $exception) {
            throw $exception->getPrevious();
        }
    }

    /**
     * @test
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_with_deferred(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('You are not authorized to access this resource');

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', 'result')->willReturn(false);

        $routeGuard = new FinalizeGuard($authorizationService->reveal());
        $routeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $deferred = $actionEvent->getParam(QueryBus::EVENT_PARAM_DEFERRED);
                $deferred->resolve('result');
                $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLED, true);
            },
            QueryBus::PRIORITY_LOCATE_HANDLER + 1000
        );

        try {
            $promise = $this->messageBus->dispatch('test_event');
            $promise->done();
        } catch (MessageDispatchException $exception) {
            throw $exception->getPrevious();
        }
    }

    /**
     * @test
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_without_deferred_and_exposes_message_name(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('You are not authorized to access the resource "test_event"');

        $this->messageBus = new EventBus();

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event')->willReturn(false);

        $routeGuard = new FinalizeGuard($authorizationService->reveal(), true);
        $routeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLED, true);
            },
            QueryBus::PRIORITY_LOCATE_HANDLER + 1000
        );

        try {
            $promise = $this->messageBus->dispatch('test_event');
            $promise->done();
        } catch (MessageDispatchException $exception) {
            throw $exception->getPrevious();
        }
    }

    /**
     * @test
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_with_deferred_and_exposes_message_name(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('You are not authorized to access the resource "test_event"');

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('test_event', 'result')->willReturn(false);

        $finalizeGuard = new FinalizeGuard($authorizationService->reveal(), true);
        $finalizeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $deferred = $actionEvent->getParam(QueryBus::EVENT_PARAM_DEFERRED);
                $deferred->resolve('result');
                $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLED, true);
            },
            QueryBus::PRIORITY_LOCATE_HANDLER + 1000
        );

        $promise = $this->messageBus->dispatch('test_event');
        $promise->done();
    }
}
