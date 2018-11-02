<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Container;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesDefaultOptions;
use Interop\Config\RequiresConfigId;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\Exception\InvalidArgumentException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\MessageFactoryPlugin;
use Prooph\ServiceBus\Plugin\Router\AsyncSwitchMessageRouter;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;
use Psr\Container\ContainerInterface;

abstract class AbstractBusFactory implements RequiresConfigId, ProvidesDefaultOptions
{
    use ConfigurationTrait;

    /**
     * Returns the FQCN of a bus extending Prooph\ServiceBus\MessageBus
     */
    abstract protected function getBusClass(): string;

    /**
     * Returns the default router class to use if no one was specified in the config
     */
    abstract protected function getDefaultRouterClass(): string;

    /**
     * @var string
     */
    private $configId;

    /**
     * Creates a new instance from a specified config, specifically meant to be used as static factory.
     *
     * In case you want to use another config key than provided by the factories, you can add the following factory to
     * your config:
     *
     * <code>
     * <?php
     * return [
     *     'prooph.service_bus.other' => [CommandBusFactory::class, 'other'],
     * ];
     * </code>
     *
     * @throws InvalidArgumentException
     */
    public static function __callStatic(string $name, array $arguments): MessageBus
    {
        if (! isset($arguments[0]) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                \sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new static($name))->__invoke($arguments[0]);
    }

    public function __construct(string $configId)
    {
        $this->configId = $configId;
    }

    public function dimensions(): iterable
    {
        return ['prooph', 'service_bus'];
    }

    public function defaultOptions(): iterable
    {
        return [
            'enable_handler_location' => true,
            'message_factory' => MessageFactory::class,
        ];
    }

    public function __invoke(ContainerInterface $container): MessageBus
    {
        $config = [];

        if ($container->has('config')) {
            $config = $container->get('config');
        }

        $busConfig = $this->optionsWithFallback($config, $this->configId);

        $busClass = $this->getBusClass();

        $bus = new $busClass();

        if (isset($busConfig['plugins'])) {
            $this->attachPlugins($bus, $busConfig['plugins'], $container);
        }

        if (isset($busConfig['router'])) {
            $this->attachRouter($bus, $busConfig['router'], $container);
        }

        if ((bool) $busConfig['enable_handler_location']) {
            (new ServiceLocatorPlugin($container))->attachToMessageBus($bus);
        }

        if ($container->has($busConfig['message_factory'])) {
            (new MessageFactoryPlugin($container->get($busConfig['message_factory'])))->attachToMessageBus($bus);
        }

        return $bus;
    }

    private function attachPlugins(MessageBus $bus, array $plugins, ContainerInterface $container): void
    {
        foreach ($plugins as $index => $plugin) {
            if (! \is_string($plugin) || ! $container->has($plugin)) {
                throw new RuntimeException(\sprintf(
                    'Wrong message bus utility configured at %s. Either it is not a string or unknown by the container.',
                    \implode('.', $this->dimensions()) . '.' . $this->configId . '.' . $index
                ));
            }

            $container->get($plugin)->attachToMessageBus($bus);
        }
    }

    private function attachRouter(MessageBus $bus, array $routerConfig, ContainerInterface $container): void
    {
        $routerClass = $routerConfig['type'] ?? $this->getDefaultRouterClass();

        $routes = $routerConfig['routes'] ?? [];

        $router = new $routerClass($routes);

        if (isset($routerConfig['async_switch'])) {
            $asyncMessageProducer = $container->get($routerConfig['async_switch']);

            $router = new AsyncSwitchMessageRouter($router, $asyncMessageProducer);
        }

        $router->attachToMessageBus($bus);
    }
}
