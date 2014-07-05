<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 23:00
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Service\EventFactoryLoader;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractEventFactoryFactory
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AbstractEventFactoryFactory implements AbstractFactoryInterface
{
    protected $eventFactory;

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
        return $serviceLocator instanceof EventFactoryLoader;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (is_null($this->eventFactory)) {
            $this->eventFactory = new EventFactory();
        }

        return $this->eventFactory;
    }
}
 