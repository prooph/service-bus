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
use Prooph\ServiceBus\Event\EventBusInterface;
use Prooph\ServiceBus\Exception\RuntimeException;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
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
     * @var EventManagerInterface
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

        $self = $this;
        $this->addInitializer(function ($instance) use ($self) {
            if ($instance instanceof ServiceLocatorAwareInterface) {
                $instance->setServiceLocator($self);
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
        if (!$this->initialized) {
            $this->initialize();
        }

        \Assert\that($aName)->nullOr()->notEmpty()->string();

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
        if (!$this->initialized) {
            $this->initialize();
        }

        \Assert\that($aName)->nullOr()->notEmpty()->string();

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
     * @return EventManagerInterface
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
}
