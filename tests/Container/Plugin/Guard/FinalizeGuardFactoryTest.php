<?php

namespace Prooph\ServiceBusTest\Container\Plugin\Guard;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prooph\ServiceBus\Container\Plugin\Guard\FinalizeGuardFactory;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\FinalizeGuard;

/**
 * Class FinalizeGuardFactoryTest
 * @package Prooph\ServiceBusTest\Container\Plugin\Guard
 */
final class FinalizeGuardFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_route_guard()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(AuthorizationService::class)->willReturn($authorizationService->reveal());

        $factory = new FinalizeGuardFactory();

        $guard = $factory($container->reveal());

        $this->assertInstanceOf(FinalizeGuard::class, $guard);
    }
}
