<?php

/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\Guard;

use PHPUnit\Framework\TestCase;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\RouteGuard;
use Prooph\ServiceBus\Plugin\Guard\UnauthorizedException;

class RouteGuardTest extends TestCase
{
    /**
     * @var CommandBus
     */
    protected $messageBus;

    protected function setUp(): void
    {
        $this->messageBus = new CommandBus();
    }

    /**
     * @test
     */
    public function it_allows_when_authorization_service_grants_access(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CommandBus was not able to identify a CommandHandler for command stdClass');

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('stdClass', new \stdClass())->willReturn(true);

        $routeGuard = new RouteGuard($authorizationService->reveal());
        $routeGuard->attachToMessageBus($this->messageBus);

        try {
            $this->messageBus->dispatch(new \stdClass());
        } catch (MessageDispatchException $e) {
            throw $e->getPrevious();
        }
    }

    /**
     * @test
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('You are not authorized to access this resource');

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('stdClass', new \stdClass())->willReturn(false);

        $routeGuard = new RouteGuard($authorizationService->reveal());
        $routeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function () {
                throw new \RuntimeException('foo');
            },
            MessageBus::PRIORITY_INVOKE_HANDLER
        );

        try {
            $this->messageBus->dispatch(new \stdClass());
        } catch (MessageDispatchException $e) {
            throw $e->getPrevious();
        }
    }

    /**
     * @test
     */
    public function it_stops_propagation_and_throws_unauthorizedexception_when_authorization_service_denies_access_and_exposed_message_name(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('You are not authorized to access the resource "stdClass"');

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('stdClass', new \stdClass())->willReturn(false);

        $routeGuard = new RouteGuard($authorizationService->reveal(), true);
        $routeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function () {
                throw new \RuntimeException('foo');
            },
            MessageBus::PRIORITY_INVOKE_HANDLER
        );

        try {
            $this->messageBus->dispatch(new \stdClass());
        } catch (MessageDispatchException $e) {
            throw $e->getPrevious();
        }
    }

    /**
     * @test
     */
    public function it_still_throws_unauthorizedexception_if_access_is_denied_while_other_finalize_listeners_are_available(): void
    {
        $this->expectException(UnauthorizedException::class);
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('stdClass', new \stdClass())->willReturn(false);

        $routeGuard = new RouteGuard($authorizationService->reveal());
        $routeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function () {
                throw new \RuntimeException('foo');
            },
            MessageBus::PRIORITY_INVOKE_HANDLER
        );

        $this->messageBus->attach(
            MessageBus::EVENT_FINALIZE,
            function () {
            },
            $priorityHigherThanTheDefaultEventEmitterPriority = 2
        );

        try {
            $this->messageBus->dispatch(new \stdClass());
        } catch (MessageDispatchException $e) {
            throw $e->getPrevious();
        }
    }

    /**
     * @test
     */
    public function it_still_throws_unauthorizedexception_if_access_is_denied_while_other_finalize_listeners_are_available_2(): void
    {
        $this->messageBus = new EventBus();

        $this->expectException(UnauthorizedException::class);
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $authorizationService->isGranted('stdClass', new \stdClass())->willReturn(false);

        $routeGuard = new RouteGuard($authorizationService->reveal());
        $routeGuard->attachToMessageBus($this->messageBus);

        $this->messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function () {
                throw new \RuntimeException('foo');
            },
            MessageBus::PRIORITY_INVOKE_HANDLER
        );

        $this->messageBus->attach(
            MessageBus::EVENT_FINALIZE,
            function () {
            },
            $priorityHigherThanTheDefaultEventEmitterPriority = 2
        );

        try {
            $this->messageBus->dispatch(new \stdClass());
        } catch (MessageDispatchException $e) {
            throw $e->getPrevious();
        }
    }
}
