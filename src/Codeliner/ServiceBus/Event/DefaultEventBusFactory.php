<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.03.14 - 16:02
 */

namespace Codeliner\ServiceBus\Event;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\EventBusManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DefaultEventBusFactory
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
        return $serviceLocator instanceof EventBusManager;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (!$serviceLocator instanceof EventBusManager) {
            throw new RuntimeException(
                sprintf(
                    "%s is used in the wrong context. It can only be used within a'
                     . ' Codeliner\ServiceBus\Service\EventBusManager",
                    get_class($this)
                )
            );
        }

        $mainServiceLocator = $serviceLocator->getServiceLocator();

        $configuration = $mainServiceLocator->get('configuration');

        if (!isset($configuration[Definition::CONFIG_ROOT])) {
            throw new RuntimeException(
                sprintf(
                    'Config root %s is missing in global configuration',
                    Definition::CONFIG_ROOT
                )
            );
        }

        $configuration = $configuration[Definition::CONFIG_ROOT];

        if (!isset($configuration[Definition::EVENT_BUS])) {
            throw new RuntimeException(
                sprintf(
                    'event_bus config is missing in %s configuration',
                    Definition::CONFIG_ROOT
                )
            );
        }

        $configuration = $configuration[Definition::EVENT_BUS];

        if (!isset($configuration[$requestedName])) {
            throw new RuntimeException(
                sprintf(
                    'Configuration for %s bus is missing in %s.%s configuration',
                    $requestedName,
                    Definition::CONFIG_ROOT,
                    Definition::EVENT_BUS
                )
            );
        }

        $configuration = $configuration[$requestedName];


        if (!isset($configuration[Definition::QUEUE])) {
            throw new RuntimeException(
                sprintf(
                    'Queue definition is missing for %s bus in %s.%s configuration',
                    $requestedName,
                    Definition::CONFIG_ROOT,
                    Definition::EVENT_BUS
                )
            );
        }

        if (!isset($configuration[Definition::MESSAGE_DISPATCHER])) {
            throw new RuntimeException(
                sprintf(
                    'MessageDispatcher alias is missing for %s bus in %s.%s configuration',
                    $requestedName,
                    Definition::CONFIG_ROOT,
                    Definition::EVENT_BUS
                )
            );
        }

        $queues = array();

        $queueManager = $mainServiceLocator->get(Definition::QUEUE_MANAGER);

        if (is_string($configuration[Definition::QUEUE])) {
            $queues[] = $queueManager->get($configuration[Definition::QUEUE]);
        } else {
            foreach ($configuration[Definition::QUEUE] as $queueDefinition) {
                $queues[] = $queueManager->get($queueDefinition);
            }
        }

        $messageDispatcher = $mainServiceLocator->get(Definition::MESSAGE_DISPATCHER_MANAGER)
            ->get($configuration[Definition::MESSAGE_DISPATCHER]);

        $eventBus = new EventBus($requestedName, $messageDispatcher, $queues);

        if ($mainServiceLocator->has(Definition::MESSAGE_FACTORY)) {
            $eventBus->setMessageFactory($mainServiceLocator->get(Definition::MESSAGE_FACTORY));
        }

        return $eventBus;
    }
}
