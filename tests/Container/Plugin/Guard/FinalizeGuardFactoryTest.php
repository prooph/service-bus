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

namespace ProophTest\ServiceBus\Container\Plugin\Guard;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prooph\ServiceBus\Container\Plugin\Guard\FinalizeGuardFactory;
use Prooph\ServiceBus\Exception\InvalidArgumentException;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\FinalizeGuard;

class FinalizeGuardFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_route_guard(): void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(AuthorizationService::class)->willReturn($authorizationService->reveal());

        $factory = new FinalizeGuardFactory();

        $guard = $factory($container->reveal());

        $this->assertInstanceOf(FinalizeGuard::class, $guard);
    }

    /**
     * @test
     */
    public function it_creates_route_guard_with_exposing_message_name(): void
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(AuthorizationService::class)->willReturn($authorizationService->reveal());

        $guard = FinalizeGuardFactory::{'exposeMessageName'}($container->reveal());

        $this->assertInstanceOf(FinalizeGuard::class, $guard);
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_when_call_static_is_used_without_container(): void
    {
        $this->expectException(InvalidArgumentException::class);

        FinalizeGuardFactory::{'exposeMessageName'}();
    }
}
