<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:23
 */

namespace Prooph\ServiceBus\Command;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Service\CommandReceiverManager;
use Prooph\ServiceBus\Service\Definition;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DefaultCommandReceiverFactory
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultCommandReceiverFactory implements AbstractFactoryInterface
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
        return $serviceLocator instanceof CommandReceiverManager;
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
        if (!$serviceLocator instanceof CommandReceiverManager) {
            throw new RuntimeException(
                sprintf(
                    "%s is used in the wrong context. It can only be used within a'
                     . ' Prooph\ServiceBus\Service\CommandReceiverManager",
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

        if (!isset($configuration[Definition::COMMAND_MAP])) {
            throw new RuntimeException(
                sprintf(
                    '%s Configuration for %s bus is missing in %s.%s configuration',
                    Definition::COMMAND_MAP,
                    $requestedName,
                    Definition::CONFIG_ROOT,
                    Definition::COMMAND_BUS
                )
            );
        }

        $commandReceiver = new CommandReceiver($configuration[Definition::COMMAND_MAP], $mainServiceLocator);

        $configuration = $mainServiceLocator->get('configuration');

        if (isset($configuration[Definition::CONFIG_ROOT][Definition::COMMAND_HANDLER_INVOKE_STRATEGIES])) {
            $commandReceiver->setInvokeStrategies(
                $configuration[Definition::CONFIG_ROOT][Definition::COMMAND_HANDLER_INVOKE_STRATEGIES]
            );
        }

        if ($mainServiceLocator->has(Definition::INVOKE_STRATEGY_MANAGER)) {
            $commandReceiver->setInvokeStrategyManager(
                $mainServiceLocator->get(Definition::INVOKE_STRATEGY_MANAGER)
            );
        }

        if ($mainServiceLocator->has(Definition::COMMAND_FACTORY)) {
            $commandReceiver->setCommandFactory($mainServiceLocator->get(Definition::COMMAND_FACTORY));
        }

        return $commandReceiver;
    }
}
