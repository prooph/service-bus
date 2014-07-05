<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 23:11
 */

namespace Prooph\ServiceBus\Message;

use Prooph\ServiceBus\Service\MessageFactoryLoader;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractMessageFactoryFactory
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AbstractMessageFactoryFactory implements AbstractFactoryInterface
{
    protected $messageFactory;

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
        return $serviceLocator instanceof MessageFactoryLoader;
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
        if (is_null($this->messageFactory)) {
            $this->messageFactory = new MessageFactory();
        }

        return $this->messageFactory;
    }
}
 