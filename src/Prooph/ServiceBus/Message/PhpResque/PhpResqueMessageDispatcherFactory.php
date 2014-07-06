<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 19.03.14 - 18:40
 */

namespace Prooph\ServiceBus\Message\PhpResque;

use Prooph\ServiceBus\Service\Definition;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PhpResqueMessageDispatcherFactory
 *
 * @package Prooph\ServiceBus\Message\PhpResque
 * @author Alexander Miertsch <contact@prooph.de>
 */
class PhpResqueMessageDispatcherFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mainServiceLocator = $serviceLocator->getServiceLocator();

        $configuration = $mainServiceLocator->get('configuration');

        $options = null;

        if (isset($configuration[Definition::MESSAGE_DISPATCHER])
            && isset($configuration[Definition::MESSAGE_DISPATCHER]['php_resque'])) {
            $resqueConfig = $configuration[Definition::MESSAGE_DISPATCHER]['php_resque'];

            $server = isset($resqueConfig['server'])? $resqueConfig['server'] : 'localhost:6379';
            $database = isset($resqueConfig['database'])? $resqueConfig['database'] : 0;

            \Resque::setBackend($server, $database);

            if (isset($resqueConfig['options'])) {
                $options = $resqueConfig['options'];
            }
        }

        return new PhpResqueMessageDispatcher($options);
    }
}
 