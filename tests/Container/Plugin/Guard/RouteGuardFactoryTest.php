<?php

namespace Prooph\ServiceBusTest\Container\Plugin\Guard;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prooph\ServiceBus\Container\Plugin\Guard\RouteGuardFactory;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\RouteGuard;

/**
 * Class RouteGuardFactoryTest
 * @package Prooph\ServiceBusTest\Container\Plugin\Guard
 */
final class RouteGuardFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_route_guard()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(AuthorizationService::class)->willReturn($authorizationService->reveal());

        $factory = new RouteGuardFactory();

        $guard = $factory($container->reveal());

        $this->assertInstanceOf(RouteGuard::class, $guard);
    }
}
