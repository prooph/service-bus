<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:14
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\EventReceiverLoader;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DefaultEventReceiverFactory
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultEventReceiverFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $name
     * @param string                  $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $serviceLocator instanceof EventReceiverLoader;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $name
     * @param string                  $requestedName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return EventReceiverInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (!$serviceLocator instanceof EventReceiverLoader) {
            throw new RuntimeException(
                sprintf(
                    "%s is used in the wrong context. It can only be used within a'
                     . ' Prooph\ServiceBus\Service\EventReceiverLoader",
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

        if (! isset($configuration[$requestedName])) {
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

        if (!isset($configuration[Definition::EVENT_MAP])) {
            throw new RuntimeException(
                sprintf(
                    '%s Configuration for %s bus is missing in %s.%s configuration',
                    Definition::EVENT_MAP,
                    $requestedName,
                    Definition::CONFIG_ROOT,
                    Definition::EVENT_BUS
                )
            );
        }

        $eventReceiver = new EventReceiver($configuration[Definition::EVENT_MAP], $mainServiceLocator);

        $configuration = $mainServiceLocator->get('configuration');

        if (isset($configuration[Definition::CONFIG_ROOT][Definition::EVENT_HANDLER_INVOKE_STRATEGIES])) {
            $eventReceiver->setInvokeStrategies(
                $configuration[Definition::CONFIG_ROOT][Definition::EVENT_HANDLER_INVOKE_STRATEGIES]
            );
        }

        if ($mainServiceLocator->has(Definition::INVOKE_STRATEGY_LOADER)) {
            $eventReceiver->setInvokeStrategyLoader(
                $mainServiceLocator->get(Definition::INVOKE_STRATEGY_LOADER)
            );
        }

        if ($mainServiceLocator->has(Definition::EVENT_FACTORY)) {
            $eventReceiver->setEventFactory($mainServiceLocator->get(Definition::EVENT_FACTORY));
        }

        return $eventReceiver;
    }
}
 