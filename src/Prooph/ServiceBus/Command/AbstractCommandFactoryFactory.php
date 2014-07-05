<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 22:51
 */

namespace Prooph\ServiceBus\Command;

use Prooph\ServiceBus\Service\CommandFactoryLoader;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractCommandFactoryFactory
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AbstractCommandFactoryFactory implements AbstractFactoryInterface
{
    protected $commandFactory;

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
        if ($serviceLocator instanceof CommandFactoryLoader) {
            return true;
        }
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
        if (is_null($this->commandFactory)) {
            $this->commandFactory = new CommandFactory();
        }
        return $this->commandFactory;
    }
}
 