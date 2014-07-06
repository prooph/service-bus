<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:14
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\EventReceiverLoader;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DefaultEventReceiverFactory
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultEventReceiverFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $name
     * @param string                  $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $serviceLocator instanceof EventReceiverLoader;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string                  $name
     * @param string                  $requestedName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return EventReceiverInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (!$serviceLocator instanceof EventReceiverLoader) {
            throw new RuntimeException(
                sprintf(
                    "%s is used in the wrong context. It can only be used within a'
                     . ' Prooph\ServiceBus\Service\EventReceiverLoader",
                    get_class($this)
                )
            );
        }

        return new EventReceiver($serviceLocator->getServiceLocator());
    }
}
 