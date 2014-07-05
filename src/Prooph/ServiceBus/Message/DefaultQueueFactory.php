<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:16
 */

namespace Prooph\ServiceBus\Message;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DefaultQueueFactory
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultQueueFactory implements AbstractFactoryInterface
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
        try {
            \Assert\that($requestedName)->notEmpty()->string();
            \Assert\that($serviceLocator)->isInstanceOf("Prooph\ServiceBus\Service\QueueLoader");
            return true;
        } catch (\InvalidArgumentException $ex) {
            return false;
        }
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return QueueInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return new Queue($requestedName);
    }
}
 