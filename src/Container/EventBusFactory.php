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

namespace Prooph\ServiceBus\Container;

use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\Router\EventRouter;

class EventBusFactory extends AbstractBusFactory
{
    public function __construct(string $configId = 'event_bus')
    {
        parent::__construct($configId);
    }

    protected function getBusClass(): string
    {
        return EventBus::class;
    }

    protected function getDefaultRouterClass(): string
    {
        return EventRouter::class;
    }
}
