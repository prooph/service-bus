<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus\Container\Plugin\Guard;

use Interop\Container\ContainerInterface;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\RouteGuard;

/**
 * Class RouteGuardFactory
 * @package Prooph\ServiceBus\Container\Plugin\Guard
 */
final class RouteGuardFactory
{
    /**
     * @param ContainerInterface $container
     * @return RouteGuard
     */
    public function __invoke(ContainerInterface $container)
    {
        $authorizationService = $container->get(AuthorizationService::class);

        return new RouteGuard($authorizationService);
    }
}
