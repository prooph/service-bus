<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 8/14/15 - 11:34 PM
 */
namespace Prooph\ServiceBus\Factory;

use Prooph\ServiceBus\Plugin\Router\QueryRouter;
use Prooph\ServiceBus\QueryBus;

/**
 * Class QueryBusFactory
 *
 * @package Prooph\ServiceBus\Factory
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
class QueryBusFactory extends AbstractBusFactory
{
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
    protected function getBusConfigKey()
    {
        return 'query_bus';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultRouterClass()
    {
        return QueryRouter::class;
    }
}
