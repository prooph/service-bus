<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2013-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus\Container;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

/**
 * Class CommandBusFactory
 *
 * @package Prooph\ServiceBus\Container
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandBusFactory extends AbstractBusFactory
{
    /**
     * @inheritdoc
     */
    public function __construct($configId = 'command_bus')
    {
        parent::__construct($configId);
    }

    /**
     * @inheritdoc
     */
    protected function getBusClass()
    {
        return CommandBus::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultRouterClass()
    {
        return CommandRouter::class;
    }
}
