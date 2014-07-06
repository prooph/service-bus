<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 19:27
 */

namespace Prooph\ServiceBus\Service;

use Codeliner\ArrayReader\ArrayReader;
use Prooph\ServiceBus\Command\AbstractCommand;
use Prooph\ServiceBus\Command\CommandBusInterface;
use Prooph\ServiceBus\Event\AbstractEvent;
use Prooph\ServiceBus\Event\EventBusInterface;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\InvokeStrategy\InvokeStrategyInterface;
use Prooph\ServiceBus\LifeCycleEvent\InitializeEvent;
use Prooph\ServiceBus\Message\MessageNameProvider;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ServiceBusManager
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class ServiceBusManager extends ServiceManager
{
    /**
     * @var EventManager
     */
    protected $events;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @var string
     */
    protected $defaultCommandBus;

    /**
     * @var string
     */
    protected $defaultEventBus;

    /**
     * @var ServiceLocatorInterface
     */
    protected $mainServiceLocator;

    /**
     * @var ArrayReader
     */
    protected $configReader;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config = null)
    {
        parent::__construct($config);

        $this->addInitializer(function ($instance) {
            if ($instance instanceof ServiceLocatorAwareInterface) {
                $instance->setServiceLocator($this->getMainServiceLocator());
            }
        });

        $this->addAbstractFactory('Prooph\ServiceBus\Service\Factory\AbstractLoaderFactory');
    }

    /**
     * @return ServiceBusManager
     */
    public function initialize()
    {
        $this->events()->trigger(new InitializeEvent($this));
        $this->initialized = true;
        return $this;
    }

    /**
     * Facade method that can handle all commands and events
     *
     * Event listener can listen to the "route" event to decide which bus should be used for a specific message
     * If a listener routes the message, it should return a boolean TRUE
     * If no listener routes the message by it's own the message is send/published directly to a handler
     * (if one is defined via direct_command/event_map) or to the related default bus
     *
     * @param mixed $message
     * @throws \Prooph\ServiceBus\Exception\RuntimeException If method could not be routed
     */
    public function route($message)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $argv = compact("message");

        $argv = $this->events()->prepareArgs($argv);

        $event = new Event(__FUNCTION__, $this, $argv);

        $result = $this->events()->triggerUntil($event, function ($res) {
            return is_bool($res)? $res : false;
        });

        if ($result->stopped()) {
            return;
        }

        if ($message instanceof AbstractCommand) {
            $this->getCommandBus()->send($message);
            return;
        }

        if ($message instanceof AbstractEvent) {
            $this->getEventBus()->publish($message);
            return;
        }

        throw new RuntimeException(
            sprintf(
                "Routing the message -%s- failed. No route event handler has handled the message and it is neither a command nor an event",
                get_class($message)
            )
        );
    }

    /**
     * The method acts as listener for the route event but can also be called by a client by passing in the message to route.
     *
     * The method checks if message class can be found in the direct_command_map or direct_event_map.
     * If so it loads the appropriate handler for the message and route the message directly to it
     * without using the messaging layer.
     *
     * The direct routing is very fast but be aware that you loose tracking capabilities if you skip the
     * messaging layer.
     *
     * @param mixed $routeEventOrMessage
     * @return bool Says true on success and false on error
     */
    public function routeDirect($routeEventOrMessage)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        if ($routeEventOrMessage instanceof Event) {
            $routeEventOrMessage = $routeEventOrMessage->getParam('message');
        }

        if (! is_object($routeEventOrMessage)) {
            return false;
        }

        $messageName = ($routeEventOrMessage instanceof MessageNameProvider)?
            $routeEventOrMessage->getMessageName() : get_class($routeEventOrMessage);

        $commandMap = $this->getConfigReader()->arrayValue(
            Definition::CONFIG_ROOT_ESCAPED . '.' . Definition::DIRECT_COMMAND_MAP
        );

        if (array_key_exists($messageName, $commandMap)) {
            $this->routeCommandTo($routeEventOrMessage, $commandMap[$messageName]);
            return true;
        }

        $eventMap =  $this->getConfigReader()->arrayValue(
            Definition::CONFIG_ROOT_ESCAPED . '.' . Definition::DIRECT_EVENT_MAP
        );

        if (array_key_exists($messageName, $eventMap)) {
            $eventHandlers = $eventMap[$messageName];

            if (! is_array($eventHandlers)) {
                $eventHandlers = array($eventHandlers);
            }

            foreach ($eventHandlers as $eventHandler) {
                $this->routeEventTo($routeEventOrMessage, $eventHandler);
            }

            return true;
        }
    }

    /**
     * The method is used to deliver a command to a concrete command handler
     *
     * It's recommended to not use this method outside the ServiceBus environment.
     * Use {@method route} instead.
     *
     * @param mixed $command
     * @param mixed $commandHandler
     */
    public function routeCommandTo($command, $commandHandler)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $argv = compact("command", "commandHandler");

        $argv = $this->events()->prepareArgs($argv);

        $event = new Event("route_command", $this, $argv);

        $result = $this->events()->triggerUntil($event, function ($res) {
            return is_bool($res)? $res : false;
        });

        if ($result->stopped()) {
            return;
        }

        $command = $event->getParam("command");
        $commandHandler = $event->getParam("commandHandler");

        if (! is_object($commandHandler)) {
            $commandHandler = $this->get($commandHandler);
        }

        $this->invokeCommandOn($command, $commandHandler);
    }

    /**
     * Detect the appropriate invoke strategy and trigger handling
     *
     * @param $command
     * @param $commandHandler
     * @throws \Prooph\ServiceBus\Exception\RuntimeException If command can not be invoked
     */
    protected function invokeCommandOn($command, $commandHandler)
    {
        $params = compact('command', 'commandHandler');

        $results = $this->events()->trigger('invoke_command.pre', $this, $params);

        if ($results->stopped()) {
            return;
        }

        $commandInvokeStrategies = $this->getConfigReader()->arrayValue(
            Definition::CONFIG_ROOT_ESCAPED . '.' . Definition::COMMAND_HANDLER_INVOKE_STRATEGIES
        );

        $invokeStrategyLoader = $this->get(Definition::INVOKE_STRATEGY_LOADER);

        $invokeStrategy = null;

        foreach ($commandInvokeStrategies as $invokeStrategyAlias) {
            /** @var $invokeStrategy InvokeStrategyInterface */
            $invokeStrategy = $invokeStrategyLoader->get($invokeStrategyAlias);

            if ($invokeStrategy->canInvoke($commandHandler, $command)) {
                break;
            }

            $invokeStrategy = null;
        }

        if (is_null($invokeStrategy)) {
            throw new RuntimeException(sprintf(
                'No InvokeStrategy can invoke command %s on handler %s',
                get_class($command),
                get_class($commandHandler)
            ));
        }

        $this->events()->trigger('invoke_command.post', $this, $params);
    }

    /**
     * The method is used to deliver an event to a concrete event handler
     *
     * It's recommended to not use this method outside the ServiceBus environment.
     * Use {@method route} instead.
     *
     * @param mixed $event
     * @param mixed $eventHandler
     */
    public function routeEventTo($event, $eventHandler)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $argv = compact("event", "eventHandler");

        $argv = $this->events()->prepareArgs($argv);

        $routeEvent = new Event("route_event", $this, $argv);

        $result = $this->events()->triggerUntil($routeEvent, function ($res) {
            return is_bool($res)? $res : false;
        });

        if ($result->stopped()) {
            return;
        }

        $event = $routeEvent->getParam("event");
        $eventHandler = $routeEvent->getParam("eventHandler");

        if (! is_object($eventHandler)) {
            $eventHandler = $this->get($eventHandler);
        }

        $this->invokeEventOn($event, $eventHandler);
    }

    /**
     * Detect the appropriate invoke strategy and trigger handling
     *
     * @param $event
     * @param $eventHandler
     * @throws \Prooph\ServiceBus\Exception\RuntimeException If event can not be invoked
     */
    protected function invokeEventOn($event, $eventHandler)
    {
        $params = compact('event', 'eventHandler');

        $results = $this->events()->trigger('invoke_event.pre', $this, $params);

        if ($results->stopped()) {
            return;
        }

        $eventInvokeStrategies = $this->getConfigReader()->arrayValue(
            Definition::CONFIG_ROOT_ESCAPED . '.' . Definition::EVENT_HANDLER_INVOKE_STRATEGIES
        );

        $invokeStrategyLoader = $this->get(Definition::INVOKE_STRATEGY_LOADER);

        $invokeStrategy = null;

        foreach ($eventInvokeStrategies as $invokeStrategyAlias) {
            /** @var $invokeStrategy InvokeStrategyInterface */
            $invokeStrategy = $invokeStrategyLoader->get($invokeStrategyAlias);

            if ($invokeStrategy->canInvoke($eventHandler, $event)) {
                break;
            }

            $invokeStrategy = null;
        }

        if (is_null($invokeStrategy)) {
            throw new RuntimeException(sprintf(
                'No InvokeStrategy can invoke event %s on handler %s',
                get_class($event),
                get_class($eventHandler)
            ));
        }

        $invokeStrategy->invoke($eventHandler, $event);

        $this->events()->trigger('invoke_event.post', $this, $params);
    }

    /**
     * @param string $aName
     */
    public function setDefaultCommandBus($aName)
    {
        \Assert\that($aName)->notEmpty()->string();

        $this->defaultCommandBus = $aName;
    }

    /**
     * @param null|string $aName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return CommandBusInterface
     */
    public function getCommandBus($aName = null)
    {
        \Assert\that($aName)->nullOr()->notEmpty()->string();

        if (!$this->initialized) {
            $this->initialize();
        }

        $argv = array("name" => $aName);

        $argv = $this->events()->prepareArgs($argv);

        $event = new Event(__FUNCTION__ . ".pre", $this, $argv);

        $result = $this->events()->triggerUntil($event, function ($res) {
            return $res instanceof CommandBusInterface;
        });

        if ($result->stopped()) {
            return $result->last();
        }

        $aName = $event->getParam('name');


        if (is_null($aName)) {
            if (is_null($this->defaultCommandBus)) {
                throw new RuntimeException(
                    sprintf(
                        'No default command bus set. Please provide a command bus name or set a default bus in %s',
                        get_class($this)
                    )
                );
            }

            return $this->get(Definition::COMMAND_BUS_LOADER)->get($this->defaultCommandBus);
        }

        return $this->get(Definition::COMMAND_BUS_LOADER)->get($aName);
    }

    /**
     * @param string $aName
     */
    public function setDefaultEventBus($aName)
    {
        \Assert\that($aName)->notEmpty()->string();

        $this->defaultEventBus = $aName;
    }

    /**
     * @param null|string $aName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return EventBusInterface
     */
    public function getEventBus($aName = null)
    {
        \Assert\that($aName)->nullOr()->notEmpty()->string();

        if (!$this->initialized) {
            $this->initialize();
        }

        $argv = array("name" => $aName);

        $argv = $this->events()->prepareArgs($argv);

        $event = new Event(__FUNCTION__ . ".pre", $this, $argv);

        $result = $this->events()->triggerUntil($event, function ($res) {
            return $res instanceof EventBusInterface;
        });

        if ($result->stopped()) {
            return $result->last();
        }

        $aName = $event->getParam('name');

        if (is_null($aName)) {
            if (is_null($this->defaultEventBus)) {
                throw new RuntimeException(
                    sprintf(
                        'No default event bus set. Please provide an event bus name or set a default bus in %s',
                        get_class($this)
                    )
                );
            }

            return $this->get(Definition::EVENT_BUS_LOADER)->get($this->defaultEventBus);
        }

        return $this->get(Definition::EVENT_BUS_LOADER)->get($aName);
    }

    /**
     * @return EventManager
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * @param EventManagerInterface $events
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            'ServiceBus',
            'ServiceBusManager',
            __CLASS__
        ));

        $events->attach('route', array($this, "routeDirect"), -1000);

        $this->events = $events;
    }

    /**
     * Reset ServiceBusManager
     */
    public function clear()
    {
        $this->events      = null;
        $this->initialized = false;
        $this->instances   = array();
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getMainServiceLocator()
    {
        if (!is_null($this->mainServiceLocator)) {
            return $this->mainServiceLocator;
        }

        return $this;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setMainServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->mainServiceLocator = $serviceLocator;
    }

    /**
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return ArrayReader
     */
    public function getConfigReader()
    {
        if (! $this->initialized) {
            throw new RuntimeException(
                "Read the config is not allowed until ServiceBusManager is not initialized"
            );
        }

        if (is_null($this->configReader)) {
            if (! $this->has('configuration')) {
                $this->configReader = new ArrayReader(array());
            } else {
                $this->configReader  = new ArrayReader($this->get('configuration'));
            }
        }

        return $this->configReader;
    }
}
