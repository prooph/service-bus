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

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresContainerId;
use Interop\Config\ProvidesDefaultOptions;
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
abstract class AbstractBusFactory implements RequiresContainerId, ProvidesDefaultOptions
{
    use ConfigurationTrait;

    /**
     * Returns the FQCN of a bus extending Prooph\ServiceBus\MessageBus
     *
     * @return string
     */
    abstract protected function getBusClass();

    /**
     * Returns the default router class to use if no one was specified in the config
     *
     * @return string
     */
    abstract protected function getDefaultRouterClass();

    /**
     * @inheritdoc
     */
    public function vendorName()
    {
        return 'prooph';
    }

    /**
     * @inheritdoc
     */
    public function packageName()
    {
        return 'service_bus';
    }

    /**
     * @inheritdoc
     */
    public function defaultOptions()
    {
        return [
            'enable_handler_location' => true,
            'message_factory' => MessageFactory::class,
        ];
    }

    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @throws RuntimeException
     * @return MessageBus
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = [];

        if ($container->has('config')) {
            $config = $container->get('config');
        }

        $busConfig = $this->optionsWithFallback($config);

        $busClass = $this->getBusClass();

        $bus = new $busClass();

        if (isset($busConfig['plugins'])) {
            $this->attachPlugins($bus, $busConfig['plugins'], $container);
        }

        if (isset($busConfig['router'])) {
            $this->attachRouter($bus, $busConfig['router']);
        }

        if ((bool) $busConfig['enable_handler_location']) {
            $bus->utilize(new ServiceLocatorPlugin($container));
        }

        if ($container->has($busConfig['message_factory'])) {
            $bus->utilize(new MessageFactoryPlugin($container->get($busConfig['message_factory'])));
        }

        return $bus;
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
                    'prooph.service_bus.' . $this->containerId() . '.' . $index
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
        $routerClass = isset($routerConfig['type']) ? (string)$routerConfig['type'] : $this->getDefaultRouterClass();

        $routes = isset($routerConfig['routes']) ? $routerConfig['routes'] : [];

        $router = new $routerClass($routes);

        $bus->utilize($router);
    }
}
