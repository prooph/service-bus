<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 21:34
 */

namespace Prooph\ServiceBus\Service\Factory;

use Codeliner\ArrayReader\ArrayReader;
use Prooph\ServiceBus\Service\Definition;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractLoaderFactory
 *
 * @package Prooph\ServiceBus\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AbstractLoaderFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $servicesMap;

    /**
     * @var ArrayReader
     */
    protected $configReader;

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return in_array($requestedName, array_keys($this->getServicesMap()));
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (is_null($this->configReader)) {
            $this->configReader = new ArrayReader($serviceLocator->get('configuration'));
        }

        $config = new Config($this->configReader->arrayValue(
            Definition::CONFIG_ROOT_ESCAPED . '.' . $requestedName
        ));

        $loaderClass = $this->getServicesMap()[$requestedName];

        return new $loaderClass($config);
    }

    /**
     * @return array
     */
    protected function getServicesMap()
    {
        if (is_null($this->servicesMap)) {
            $this->servicesMap = array(
                Definition::COMMAND_BUS_LOADER => 'Prooph\ServiceBus\Service\CommandBusLoader',
                Definition::COMMAND_RECEIVER_LOADER => 'Prooph\ServiceBus\Service\CommandReceiverLoader',
                Definition::EVENT_BUS_LOADER => 'Prooph\ServiceBus\Service\EventBusLoader',
                Definition::EVENT_RECEIVER_LOADER => 'Prooph\ServiceBus\Service\EventReceiverLoader',
                Definition::INVOKE_STRATEGY_LOADER => 'Prooph\ServiceBus\Service\InvokeStrategyLoader',
                Definition::MESSAGE_DISPATCHER_LOADER => 'Prooph\ServiceBus\Service\MessageDispatcherLoader',
                Definition::QUEUE_LOADER => 'Prooph\ServiceBus\Service\QueueLoader',
                Definition::COMMAND_FACTORY_LOADER => 'Prooph\ServiceBus\Service\CommandFactoryLoader',
                Definition::EVENT_FACTORY_LOADER   => 'Prooph\ServiceBus\Service\EventFactoryLoader'
            );
        }

        return $this->servicesMap;
    }
}
 