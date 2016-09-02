<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    /**
     * @inheritdoc
     */
    public function __construct($configId = 'query_bus')
    {
        parent::__construct($configId);
    }

    /**
     * @inheritdoc
     */
    protected function getBusClass()
    {
        return QueryBus::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultRouterClass()
    {
        return QueryRouter::class;
    }
}
