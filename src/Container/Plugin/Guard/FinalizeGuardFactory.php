<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 09/13/15 - 15:30
 */

namespace Prooph\ServiceBus\Container\Plugin\Guard;

use Interop\Container\ContainerInterface;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\FinalizeGuard;

/**
 * Class FinalizeGuardFactory
 * @package Prooph\ServiceBus\Container\Plugin\Guard
 */
final class FinalizeGuardFactory
{
    /**
     * @param ContainerInterface $container
     * @return FinalizeGuard
     */
    public function __invoke(ContainerInterface $container)
    {
        $authorizationService = $container->get(AuthorizationService::class);

        return new FinalizeGuard($authorizationService);
    }
}
