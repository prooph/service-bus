<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 14:35
 */

namespace Codeliner\ServiceBus\Service;

class StaticServiceBusRegistry
{
    /**
     * @var ServiceBusConfiguration
     */
    protected static $configuration;

    /**
     * @param array|ServiceBusConfiguration $aConfiguration
     */
    public static function setConfiguration($aConfiguration)
    {
        if (is_array($aConfiguration)) {
            $aConfiguration = new ServiceBusConfiguration($aConfiguration);
        }

        \Assert\that($aConfiguration)->isInstanceOf('Codeliner\ServiceBus\Service\ServiceBusConfiguration');

        static::$configuration = $aConfiguration;
    }

    /**
     * @return ServiceBusConfiguration
     */
    public static function getConfiguration()
    {
        if (is_null(static::$configuration)) {
            static::tryReadConfigFromFilesystem();
        }

        return static::$configuration;
    }

    /**
     * Reset registry
     */
    public static function reset()
    {
        static::$configuration = null;
    }

    /**
     * @throws \RuntimeException
     */
    protected static function tryReadConfigFromFilesystem()
    {
        if (file_exists('service-bus-config.php')) {
            $config = include 'service-bus-config.php';
            goto CONSTRUCT_CONFIG;
        }

        if (file_exists('service-bus-config.php.dist')) {
            $config = include 'service-bus-config.php.dist';
            goto CONSTRUCT_CONFIG;
        }

        if (file_exists('config/service-bus-config.php')) {
            $config = include 'service-bus-config.php';
            goto CONSTRUCT_CONFIG;
        }

        if (file_exists('config/service-bus-config.php.dist')) {
            $config = include 'service-bus-config.php.dist';
            goto CONSTRUCT_CONFIG;
        }

        throw new \RuntimeException('Can not find a service-bus-config.php');


        CONSTRUCT_CONFIG:
        static::$configuration = new ServiceBusConfiguration($config);
    }
}
