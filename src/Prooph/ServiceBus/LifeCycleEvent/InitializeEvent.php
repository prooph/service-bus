<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.06.14 - 21:57
 */

namespace Prooph\ServiceBus\LifeCycleEvent;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Zend\EventManager\Event;

/**
 * Class InitializeEvent
 *
 * @package Prooph\ServiceBus\LifeCycleEvent
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class InitializeEvent extends Event
{
    const NAME = "initializeServiceBus";

    /**
     * @param ServiceBusManager $serviceBusManager
     */
    public function __construct(ServiceBusManager $serviceBusManager)
    {
        $this->setName(self::NAME);
        $this->setTarget($serviceBusManager);
    }

    /**
     * @return ServiceBusManager
     */
    public function getServiceBusManager()
    {
        return $this->getTarget();
    }
}
 