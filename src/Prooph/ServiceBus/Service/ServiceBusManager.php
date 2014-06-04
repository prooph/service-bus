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

use Prooph\ServiceBus\Command\CommandBusInterface;
use Prooph\ServiceBus\Command\CommandInterface;
use Prooph\ServiceBus\Event\EventBusInterface;
use Prooph\ServiceBus\Event\EventInterface;
use Prooph\ServiceBus\Exception\RuntimeException;
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
     * @var array
     */
    protected $invokableClasses = array(
        'commandbusmanager'         => 'Prooph\ServiceBus\Service\CommandBusManager',
        'commandreceivermanager'    => 'Prooph\ServiceBus\Service\CommandReceiverManager',
        'invokestrategymanager'     => 'Prooph\ServiceBus\Service\InvokeStrategyManager',
        'messagedispatchermanager'  => 'Prooph\ServiceBus\Service\MessageDispatcherManager',
        'queuemanager'              => 'Prooph\ServiceBus\Service\QueueManager',
        'eventreceivermanager'      => 'Prooph\ServiceBus\Service\EventReceiverManager',
        'eventbusmanager'           => 'Prooph\ServiceBus\Service\EventBusManager',
    );

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
    }

    /**
     * @return ServiceBusManager
     */
    public function initialize()
    {
        $this->events()->trigger(__FUNCTION__, $this);
        $this->initialized = true;
        return $this;
    }

    /**
     * Facade method that can handle all commands and events
     *
     * Event listener can listen to the "route" event to decide which bus should be used for a specific message
     * If a listener routes the message, it should return a boolean TRUE
     * If no listener routes the message by it's own the message is send/publish to the related default bus
     *
     * @param mixed $message
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function route($message)
    {
        $argv = compact("message");

        $argv = $this->events()->prepareArgs($argv);

        $event = new Event(__FUNCTION__, $this, $argv);

        $result = $this->events()->triggerUntil($event, function ($res) {
            return is_bool($res)? $res : false;
        });

        if ($result->stopped()) {
            return;
        }

        if ($message instanceof CommandInterface) {
            $this->getCommandBus()->send($message);
            return;
        }

        if ($message instanceof EventInterface) {
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

            return $this->get('commandbusmanager')->get($this->defaultCommandBus);
        }

        return $this->get('commandbusmanager')->get($aName);
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

            return $this->get('eventbusmanager')->get($this->defaultEventBus);
        }

        return $this->get('eventbusmanager')->get($aName);
    }

    /**
     * @return EventManager
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->events = new EventManager(array(
                'ServiceBus',
                'ServiceBusManager',
                __CLASS__
            ));
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
}
