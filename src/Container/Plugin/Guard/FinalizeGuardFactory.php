<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
