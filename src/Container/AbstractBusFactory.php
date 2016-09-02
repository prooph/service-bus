<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus\Container;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfigId;
use Interop\Config\ProvidesDefaultOptions;
use Interop\Container\ContainerInterface;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\Exception\InvalidArgumentException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\MessageFactoryPlugin;
use Prooph\ServiceBus\Plugin\Router\AsyncSwitchMessageRouter;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;

/**
 * Class AbstractBusFactory
 *
 * @package Prooph\ServiceBus\Container
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
abstract class AbstractBusFactory implements RequiresConfigId, ProvidesDefaultOptions
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
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function __callStatic($name, array $arguments)
    {
        if (!isset($arguments[0]) || !$arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }
        return (new static($name))->__invoke($arguments[0]);
    }

    /**
     * @param string $configId
     */
    public function __construct($configId)
    {
        // ensure BC
        $this->configId = method_exists($this, 'containerId') ? $this->containerId() : $configId;
    }

    /**
     * @inheritdoc
     */
    public function dimensions()
    {
        return ['prooph', 'service_bus'];
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
    private function attachPlugins(MessageBus $bus, array $utils, ContainerInterface $container)
    {
        foreach ($utils as $index => $util) {
            if (! is_string($util) || ! $container->has($util)) {
                throw new RuntimeException(sprintf(
                    'Wrong message bus utility configured at %s. Either it is not a string or unknown by the container.',
                    implode('.', $this->dimensions()) . '.' . $this->configId . '.' . $index
                ));
            }

            $bus->utilize($container->get($util));
        }
    }

    /**
     * @param MessageBus $bus
     * @param array $routerConfig
     * @param ContainerInterface $container
     */
    private function attachRouter(MessageBus $bus, array $routerConfig, ContainerInterface $container)
    {
        $routerClass = isset($routerConfig['type']) ? (string)$routerConfig['type'] : $this->getDefaultRouterClass();

        $routes = isset($routerConfig['routes']) ? $routerConfig['routes'] : [];

        $router = new $routerClass($routes);

        if (isset($routerConfig['async_switch'])) {
            $asyncMessageProducer = $container->get($routerConfig['async_switch']);

            $router = new AsyncSwitchMessageRouter($router, $asyncMessageProducer);
        }

        $bus->utilize($router);
    }
}
