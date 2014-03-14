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
}
