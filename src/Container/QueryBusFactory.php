<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Prooph\ServiceBus\Container;

use Prooph\ServiceBus\Plugin\Router\QueryRouter;
use Prooph\ServiceBus\QueryBus;

/**
 * Class QueryBusFactory
 *
 * @package Prooph\ServiceBus\Container
 */
class QueryBusFactory extends AbstractBusFactory
{
    public function __construct(string $configId = 'query_bus')
    {
        parent::__construct($configId);
    }

    protected function getBusClass() : string
    {
        return QueryBus::class;
    }

    protected function getDefaultRouterClass() : string
    {
        return QueryRouter::class;
    }
}
