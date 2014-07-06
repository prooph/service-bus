<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:23
 */

namespace Prooph\ServiceBus\Command;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Service\CommandReceiverLoader;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DefaultCommandReceiverFactory
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultCommandReceiverFactory implements AbstractFactoryInterface
{
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
        return $serviceLocator instanceof CommandReceiverLoader;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (!$serviceLocator instanceof CommandReceiverLoader) {
            throw new RuntimeException(
                sprintf(
                    "%s is used in the wrong context. It can only be used within a'
                     . ' Prooph\ServiceBus\Service\CommandReceiverLoader",
                    get_class($this)
                )
            );
        }

        return new CommandReceiver($serviceLocator->getServiceLocator());
    }
}
