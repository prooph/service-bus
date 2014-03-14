<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 19:27
 */

namespace Codeliner\ServiceBus\Service;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ServiceBusManager
 *
 * @package Codeliner\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
     * @var array
     */
    protected $invokableClasses = array(
        'commandbusmanager'         => 'Codeliner\ServiceBus\Service\CommandBusManager',
        'commandreceivermanager'    => 'Codeliner\ServiceBus\Service\CommandReceiverManager',
        'invokestrategymanager'     => 'Codeliner\ServiceBus\Service\InvokeStrategyManager',
        'messagedispatchermanager'  => 'Codeliner\ServiceBus\Service\MessageDispatcherManager',
        'queuemanager'              => 'Codeliner\ServiceBus\Service\QueueManager',
        'eventreceivermanager'      => 'Codeliner\ServiceBus\Service\EventReceiverManager',
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
        return $this;
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
