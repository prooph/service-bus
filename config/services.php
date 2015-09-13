<?php

namespace Prooph\ServiceBus;

return [
    'factories' => [
        Plugin\Guard\RouteGuard::class => Container\Plugin\Guard\RouteGuardFactory::class,
        Plugin\Guard\FinalizeGuard::class => Container\Plugin\Guard\FinalizeGuardFactory::class,
    ]
];
