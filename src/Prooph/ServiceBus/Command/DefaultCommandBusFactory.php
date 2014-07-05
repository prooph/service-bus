<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:37
 */

namespace Prooph\ServiceBus\Command;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Service\CommandBusLoader;
use Prooph\ServiceBus\Service\Definition;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DefaultCommandBusFactory
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultCommandBusFactory implements AbstractFactoryInterface
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
        return $serviceLocator instanceof CommandBusLoader;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return CommandBus
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (!$serviceLocator instanceof CommandBusLoader) {
            throw new RuntimeException(
                sprintf(
                    "%s is used in the wrong context. It can only be used within a'
                     . ' Prooph\ServiceBus\Service\CommandBusLoader",
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

        if (!isset($configuration[Definition::COMMAND_BUS])) {
            throw new RuntimeException(
                sprintf(
                    'command_bus config is missing in %s configuration',
                    Definition::CONFIG_ROOT
                )
            );
        }

        $configuration = $configuration[Definition::COMMAND_BUS];

        if (!isset($configuration[$requestedName])) {
            throw new RuntimeException(
                sprintf(
                    'Configuration for %s bus is missing in %s.%s configuration',
                    $requestedName,
                    Definition::CONFIG_ROOT,
                    Definition::COMMAND_BUS
                )
            );
        }

        $configuration = $configuration[$requestedName];


        if (!isset($configuration[Definition::QUEUE])) {
            throw new RuntimeException(
                sprintf(
                    'Queue alias is missing for %s bus in %s.%s configuration',
                    $requestedName,
                    Definition::CONFIG_ROOT,
                    Definition::COMMAND_BUS
                )
            );
        }

        if (!isset($configuration[Definition::MESSAGE_DISPATCHER])) {
            throw new RuntimeException(
                sprintf(
                    'MessageDispatcher alias is missing for %s bus in %s.%s configuration',
                    $requestedName,
                    Definition::CONFIG_ROOT,
                    Definition::COMMAND_BUS
                )
            );
        }

        $queue = $mainServiceLocator->get(Definition::QUEUE_LOADER)->get($configuration[Definition::QUEUE]);

        $messageDispatcher = $mainServiceLocator->get(Definition::MESSAGE_DISPATCHER_LOADER)
            ->get($configuration[Definition::MESSAGE_DISPATCHER]);

        $commandBus = new CommandBus($requestedName, $messageDispatcher, $queue);


        $commandBus->setMessageFactoryLoader($mainServiceLocator->get(Definition::MESSAGE_FACTORY_LOADER));


        return $commandBus;
    }
}
 