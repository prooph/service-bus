<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProophTest\ServiceBus\Container\Plugin\Guard;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prooph\ServiceBus\Container\Plugin\Guard\RouteGuardFactory;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\RouteGuard;

/**
 * Class RouteGuardFactoryTest
 * @package ProophTest\ServiceBus\Container\Plugin\Guard
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

    /**
     * @test
     */
    public function it_creates_route_guard_with_exposing_message_name()
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(AuthorizationService::class)->willReturn($authorizationService->reveal());

        $guard = RouteGuardFactory::{'exposeMessageName'}($container->reveal());

        $this->assertInstanceOf(RouteGuard::class, $guard);
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\InvalidArgumentException
     */
    public function it_throws_invalid_argument_exception_when_call_static_is_used_without_container()
    {
        RouteGuardFactory::{'exposeMessageName'}();
    }
}
