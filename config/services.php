<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2021 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus;

return [
    'factories' => [
        Plugin\Guard\RouteGuard::class => Container\Plugin\Guard\RouteGuardFactory::class,
        Plugin\Guard\FinalizeGuard::class => Container\Plugin\Guard\FinalizeGuardFactory::class,
        // static factory calls with individual config id
        'special_command_bus' => [\Prooph\ServiceBus\Container\CommandBusFactory::class, 'special'],
        'special_event_bus' => [\Prooph\ServiceBus\Container\EventBusFactory::class, 'special'],
        'special_query_bus' => [\Prooph\ServiceBus\Container\QueryBusFactory::class, 'special'],
        // to expose message name in UnauthorizedException
        Plugin\Guard\RouteGuard::class => [
            Container\Plugin\Guard\RouteGuardFactory::class,
            'exposeMessageName',
        ],
        Plugin\Guard\FinalizeGuard::class => [
            Container\Plugin\Guard\FinalizeGuardFactory::class,
            'exposeMessageName',
        ],
    ],
];
