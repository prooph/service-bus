<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 21:05
 */

namespace Prooph\ServiceBusTest\Mock\Configtest;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CustomBusFactory
 *
 * @package Prooph\ServiceBusTest\Mock\Configtest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CustomBusFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CustomBus("Created via factory");
    }
}
 