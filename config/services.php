<?php

namespace Prooph\ServiceBus;

return [
    'factories' => [
        Plugin\Guard\RouteGuard::class => Container\Plugin\Guard\RouteGuardFactory::class,
        Plugin\Guard\FinalizeGuard::class => Container\Plugin\Guard\FinalizeGuardFactory::class,
        // static factory calls with individual config id
        'special_command_bus' => [\Prooph\ServiceBus\Container\CommandBusFactory::class, 'special'],
        'special_event_bus' => [\Prooph\ServiceBus\Container\EventBusFactory::class, 'special'],
        'special_query_bus' => [\Prooph\ServiceBus\Container\QueryBusFactory::class, 'special'],
    ]
];
