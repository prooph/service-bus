<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 10/30/14 - 12:57
 */

namespace Prooph\ServiceBus\Container;

use Interop\Container\ContainerInterface;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\MessageFactoryPlugin;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;

/**
 * Class AbstractBusFactory
 *
 * @package Prooph\ServiceBus\Container
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
abstract class AbstractBusFactory
{
    /**
     * Returns the FQCN of a bus extending Prooph\ServiceBus\MessageBus
     *
     * @return string
     */
    abstract protected function getBusClass();

    /**
     * Returns config key used within the prooph.service_bus config namespace to identify the bus.
     *
     * @return string
     */
    abstract protected function getBusConfigKey();

    /**
     * Returns the default router class to use if no one was specified in the config
     *
     * @return string
     */
    abstract protected function getDefaultRouterClass();

    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @throws RuntimeException
     * @return MessageBus
     */
    public function __invoke(ContainerInterface $container)
    {
        $busConfig = $this->getBusConfig($container);

        $busClass = $this->getBusClass();

        $bus = new $busClass();

        if (isset($busConfig['plugins'])) {
            $this->attachPlugins($bus, $busConfig['plugins'], $container);
        }

        if (isset($busConfig['router'])) {
            $this->attachRouter($bus, $busConfig['router']);
        }

        if (!isset($busConfig['enable_handler_location']) || (bool)$busConfig['enable_handler_location']) {
            $bus->utilize(new ServiceLocatorPlugin($container));
        }

        $messageFactoryServiceId = isset($busConfig['message_factory'])
            ? $busConfig['message_factory']
            : MessageFactory::class;

        if ($container->has($messageFactoryServiceId)) {
            $bus->utilize(new MessageFactoryPlugin($container->get($messageFactoryServiceId)));
        }

        return $bus;
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws RuntimeException
     */
    private function getBusConfig(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return [];
        }

        $config = $container->get('config');

        if (!is_array($config) && !$config instanceof \ArrayAccess) {
            throw new RuntimeException(sprintf(
                'Application config registered in the container %s must be either of type array or implement \ArrayAccess. Otherwise it is not compatible with %s',
                get_class($container),
                get_called_class()
            ));
        }

        if (!isset($config['prooph']['service_bus'][$this->getBusConfigKey()])) {
            return [];
        }

        $busConfig = $config['prooph']['service_bus'][$this->getBusConfigKey()];

        if (!is_array($busConfig) && !$busConfig instanceof \ArrayAccess) {
            throw new RuntimeException(sprintf(
                'Config prooph.service_bus.%s must either be of type array or implement \ArrayAccess.',
                $this->getBusConfigKey()
            ));
        }

        return $busConfig;
    }

    /**
     * @param MessageBus $bus
     * @param array $utils
     * @param ContainerInterface $container
     * @throws RuntimeException
     */
    private function attachPlugins(MessageBus $bus, array &$utils, ContainerInterface $container)
    {
        foreach ($utils as $index => $util) {
            if (! is_string($util) || ! $container->has($util)) {
                throw new RuntimeException(sprintf(
                    'Wrong message bus utility configured at %s. Either it is not a string or unknown by the container.',
                    'prooph.service_bus.' . $this->getBusConfigKey() . '.' . $index
                ));
            }

            $bus->utilize($container->get($util));
        }
    }

    /**
     * @param MessageBus $bus
     * @param array $routerConfig
     */
    private function attachRouter(MessageBus $bus, array &$routerConfig)
    {
        $routerClass = isset($routerConfig['type'])? (string)$routerConfig['type'] : $this->getDefaultRouterClass();

        $routes = isset($routerConfig['routes'])? $routerConfig['routes'] : [];

        $router = new $routerClass($routes);

        $bus->utilize($router);
    }
}
