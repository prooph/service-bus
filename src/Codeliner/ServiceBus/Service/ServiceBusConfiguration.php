<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 22:44
 */

namespace Codeliner\ServiceBus\Service;

use Codeliner\ServiceBus\Command\CommandFactoryInterface;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class ServiceBusConfiguration implements ConfigInterface
{
    /**
     * @var array
     */
    private $configuration = array(
        Definition::CONFIG_ROOT => array(
            Definition::COMMAND_BUS => array(),
        )
    );

    /**
     * @var array
     */
    private $commandHandlers = array();

    /**
     * @var CommandFactoryInterface
     */
    private $commandFactory;

    /**
     * @var ServiceLocatorInterface
     */
    private $invokeStrategyManager;

    /**
     * @var ServiceLocatorInterface
     */
    private $commandReceiverManager;

    /**
     * Configure service manager
     *
     * @param ServiceManager $serviceManager
     * @return void
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $serviceManager->setService('configuration', $this->configuration);

        if (count($this->commandHandlers)) {
            foreach ($this->commandHandlers as $commandHandler) {
                $serviceManager->setService(get_class($commandHandler), $commandHandler);
            }
        }

        if (!is_null($this->commandFactory)) {
            $serviceManager->setService(Definition::COMMAND_FACTORY, $this->commandFactory);
        }

        if (!is_null($this->invokeStrategyManager)) {
            $serviceManager->setService(Definition::INVOKE_STRATEGY_MANAGER, $this->invokeStrategyManager);
        }

        if (!is_null($this->commandReceiverManager)) {
            $serviceManager->setService(Definition::COMMAND_RECEIVER_MANAGER, $this->commandReceiverManager);
        }
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        if (array_key_exists(Definition::CONFIG_ROOT, $configuration)) {
            $configuration = array(Definition::CONFIG_ROOT => $configuration);
        }

        $this->configuration = $configuration;
    }

    /**
     * @param array $configuration
     */
    public function setCommandBusConfiguration(array $configuration)
    {
        $this->configuration[Definition::CONFIG_ROOT][Definition::COMMAND_BUS] = $configuration;
    }

    /**
     * @param array $invokeStrategies
     */
    public function setCommandHandlerInvokeStrategies(array $invokeStrategies)
    {
        $this->configuration[Definition::CONFIG_ROOT][Definition::COMMAND_HANDLER_INVOKE_STRATEGIES] = $invokeStrategies;
    }

    /**
     * @param $commandBus
     * @param array $commandMap
     */
    public function setCommandMap($commandBus, array $commandMap)
    {
        $this->configuration[Definition::CONFIG_ROOT][Definition::COMMAND_BUS][$commandBus] = $commandMap;
    }

    /**
     * @param $commandHandler
     */
    public function addCommandHandler($commandHandler)
    {
        $this->commandHandlers[] = $commandHandler;
    }

    /**
     * @param CommandFactoryInterface $commandFactory
     */
    public function setCommandFactory(CommandFactoryInterface $commandFactory)
    {
        $this->commandFactory = $commandFactory;
    }

    /**
     * @param ServiceLocatorInterface $commandReceiverManager
     */
    public function setCommandReceiverManager(ServiceLocatorInterface $commandReceiverManager)
    {
        $this->commandReceiverManager = $commandReceiverManager;
    }

    /**
     * @param ServiceLocatorInterface $invokeStrategyManager
     */
    public function setInvokeStrategyManager(ServiceLocatorInterface $invokeStrategyManager)
    {
        $this->invokeStrategyManager = $invokeStrategyManager;
    }
}
 