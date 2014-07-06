<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.03.14 - 16:02
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\EventBusLoader;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DefaultEventBusFactory
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultEventBusFactory implements AbstractFactoryInterface
{
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
        return $serviceLocator instanceof EventBusLoader;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (!$serviceLocator instanceof EventBusLoader) {
            throw new RuntimeException(
                sprintf(
                    "%s is used in the wrong context. It can only be used within a'
                     . ' Prooph\ServiceBus\Service\EventBusLoader",
                    get_class($this)
                )
            );
        }

        $mainServiceLocator = $serviceLocator->getServiceLocator();

        $configuration = $mainServiceLocator->get('configuration');

        if (!isset($configuration[Definition::EVENT_BUS])) {
            throw new RuntimeException('event_bus config is missing in %s configuration');
        }

        $configuration = $configuration[Definition::EVENT_BUS];

        if (!isset($configuration[$requestedName])) {
            throw new RuntimeException(
                sprintf(
                    'Configuration for %s bus is missing in %s configuration',
                    $requestedName,
                    Definition::EVENT_BUS
                )
            );
        }

        $configuration = $configuration[$requestedName];

        if (!isset($configuration[Definition::MESSAGE_DISPATCHER])) {
            throw new RuntimeException(
                sprintf(
                    'MessageDispatcher alias is missing for %s bus in %s configuration',
                    $requestedName,
                    Definition::EVENT_BUS
                )
            );
        }

        $messageDispatcher = $mainServiceLocator->get(Definition::MESSAGE_DISPATCHER_LOADER)
            ->get($configuration[Definition::MESSAGE_DISPATCHER]);

        $eventBus = new EventBus($requestedName, $messageDispatcher);

        $eventBus->setMessageFactoryLoader($mainServiceLocator->get(Definition::MESSAGE_FACTORY_LOADER));

        return $eventBus;
    }
}
