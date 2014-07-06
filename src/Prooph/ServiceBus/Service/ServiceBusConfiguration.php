<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 22:44
 */

namespace Prooph\ServiceBus\Service;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceManager;

class ServiceBusConfiguration implements ConfigInterface
{
    /**
     * @var array
     */
    protected $configuration = array(
        Definition::CONFIG_ROOT => array(
            Definition::SERVICE_BUS_MANAGER => array(),
            Definition::COMMAND_BUS => array(),
            Definition::EVENT_BUS => array(),
        )
    );

    /**
     * @var array
     */
    protected $services = array();

    /**
     * @param null|array $aConfiguration
     */
    public function __construct(array $aConfiguration = null)
    {
        if (is_array($aConfiguration)) {
            $this->setConfiguration($aConfiguration);
        }
    }

    /**
     * Configure service manager
     *
     * @param ServiceManager $serviceManager
     * @return void
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $serviceManager->setService('configuration', $this->configuration);

        $serviceManager->setAllowOverride(true);

        if (count($this->services)) {
            foreach ($this->services as $alias => $service) {
                $serviceManager->setService($alias, $service);
            }
        }

        $serviceBusManagerServicesConfig = new Config(
            $this->configuration[Definition::CONFIG_ROOT][Definition::SERVICE_BUS_MANAGER]
        );

        $serviceBusManagerServicesConfig->configureServiceManager($serviceManager);

        if (isset($this->configuration[Definition::CONFIG_ROOT][Definition::DEFAULT_COMMAND_BUS])) {
            $serviceManager->setDefaultCommandBus(
                $this->configuration[Definition::CONFIG_ROOT][Definition::DEFAULT_COMMAND_BUS]
            );
        }

        if (isset($this->configuration[Definition::CONFIG_ROOT][Definition::DEFAULT_EVENT_BUS])) {
            $serviceManager->setDefaultEventBus(
                $this->configuration[Definition::CONFIG_ROOT][Definition::DEFAULT_EVENT_BUS]
            );
        }

        $serviceManager->setAllowOverride(false);
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        if (! array_key_exists(Definition::CONFIG_ROOT, $configuration)) {
            $configuration = array(Definition::CONFIG_ROOT => $configuration);
        }

        if (! array_key_exists("service_bus_manager", $configuration[Definition::CONFIG_ROOT])) {
            $configuration[Definition::CONFIG_ROOT]["service_bus_manager"] = array();
        }

        $this->configuration = $configuration;
    }

    /**
     * @param array $invokeStrategies
     */
    public function setCommandHandlerInvokeStrategies(array $invokeStrategies)
    {
        $this->configuration[Definition::CONFIG_ROOT][Definition::COMMAND_HANDLER_INVOKE_STRATEGIES] = $invokeStrategies;
    }

    /**
     * @param array $invokeStrategies
     */
    public function setEventHandlerInvokeStrategies(array $invokeStrategies)
    {
        $this->configuration[Definition::CONFIG_ROOT][Definition::EVENT_HANDLER_INVOKE_STRATEGIES] = $invokeStrategies;
    }

    /**
     * @param string $commandBusOrDirectCommandMap
     * @param array $commandMap
     */
    public function setCommandMapFor($commandBusOrDirectCommandMap, array $commandMap)
    {
        if ($commandBusOrDirectCommandMap === Definition::DIRECT_COMMAND_MAP) {
            $this->configuration[Definition::DIRECT_COMMAND_MAP] = $commandMap;
            return;
        }

        $this->configuration[Definition::CONFIG_ROOT]
            [Definition::COMMAND_BUS]
            [$commandBusOrDirectCommandMap]
            [Definition::COMMAND_MAP] = $commandMap;
    }

    /**
     * @param string $eventBusOrDirectEventMap
     * @param array $eventMap
     */
    public function setEventMapFor($eventBusOrDirectEventMap, array $eventMap)
    {
        if ($eventBusOrDirectEventMap === Definition::DIRECT_EVENT_MAP) {
            $this->configuration[Definition::DIRECT_COMMAND_MAP] = $eventMap;
            return;
        }

        $this->configuration[Definition::CONFIG_ROOT]
        [Definition::EVENT_BUS]
        [$eventBusOrDirectEventMap]
        [Definition::EVENT_MAP] = $eventMap;
    }

    /**
     * @param mixed      $aliasOrCommandHandler
     * @param null|mixed $commandHandler
     */
    public function addCommandHandler($aliasOrCommandHandler, $commandHandler = null)
    {
        if (is_null($commandHandler)) {
            $commandHandler = $aliasOrCommandHandler;
            $aliasOrCommandHandler = get_class($commandHandler);
        }

        $this->services[$aliasOrCommandHandler] = $commandHandler;
    }

    /**
     * @param mixed      $aliasOrEventHandler
     * @param null|mixed $eventHandler
     */
    public function addEventHandler($aliasOrEventHandler, $eventHandler = null)
    {
        if (is_null($eventHandler)) {
            $eventHandler = $aliasOrEventHandler;
            $aliasOrEventHandler = get_class($eventHandler);
        }

        $this->services[$aliasOrEventHandler] = $eventHandler;
    }
}
 