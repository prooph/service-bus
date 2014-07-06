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
    protected $configuration = array();

    protected $defaultConfig = array(
        Definition::SERVICE_BUS_MANAGER => array(),
        Definition::COMMAND_BUS => array(),
        Definition::EVENT_BUS => array(),
        Definition::COMMAND_MAP => array(),
        Definition::EVENT_MAP => array(),
        Definition::COMMAND_HANDLER_INVOKE_STRATEGIES => array(
            "callback_strategy",
            "handle_command_strategy"
        ),
        Definition::EVENT_HANDLER_INVOKE_STRATEGIES => array(
            "callback_strategy",
            "on_event_strategy"
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
            $this->configuration[Definition::SERVICE_BUS_MANAGER]
        );

        $serviceBusManagerServicesConfig->configureServiceManager($serviceManager);

        if (isset($this->configuration[Definition::DEFAULT_COMMAND_BUS])) {
            $serviceManager->setDefaultCommandBus(
                $this->configuration[Definition::DEFAULT_COMMAND_BUS]
            );
        }

        if (isset($this->configuration[Definition::DEFAULT_EVENT_BUS])) {
            $serviceManager->setDefaultEventBus(
                $this->configuration[Definition::DEFAULT_EVENT_BUS]
            );
        }

        $serviceManager->setAllowOverride(false);
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $configuration = array_merge_recursive($this->defaultConfig, $configuration);

        $this->configuration = $configuration;
    }

    /**
     * @param array $invokeStrategies
     */
    public function setCommandHandlerInvokeStrategies(array $invokeStrategies)
    {
        $this->configuration[Definition::COMMAND_HANDLER_INVOKE_STRATEGIES] = $invokeStrategies;
    }

    /**
     * @param array $invokeStrategies
     */
    public function setEventHandlerInvokeStrategies(array $invokeStrategies)
    {
        $this->configuration[Definition::EVENT_HANDLER_INVOKE_STRATEGIES] = $invokeStrategies;
    }

    /**
     * @param array $commandMap
     */
    public function setCommandMap(array $commandMap)
    {
        $this->configuration[Definition::COMMAND_MAP] = $commandMap;
    }

    /**
     * @param array $eventMap
     */
    public function setEventMap(array $eventMap)
    {
        $this->configuration[Definition::EVENT_MAP] = $eventMap;
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
 