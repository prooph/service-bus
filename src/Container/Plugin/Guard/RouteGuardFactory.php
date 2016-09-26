<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Prooph\ServiceBus\Container\Plugin\Guard;

use Interop\Container\ContainerInterface;
use Prooph\ServiceBus\Exception\InvalidArgumentException;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\RouteGuard;

/**
 * Class RouteGuardFactory
 * @package Prooph\ServiceBus\Container\Plugin\Guard
 */
final class RouteGuardFactory
{
    /**
     * @var bool
     */
    private $exposeEventMessageName;

    /**
     * RouteGuardFactory constructor.
     * @param bool $exposeEventMessageName
     */
    public function __construct($exposeEventMessageName = false)
    {
        $this->exposeEventMessageName = $exposeEventMessageName;
    }

    /**
     * Creates a new instance with exposeMessageName flag, specifically meant to be used as static factory.
     *
     * Configuration example:
     *
     * <code>
     * <?php
     * return [
     *     \Prooph\ServiceBus\Plugin\Guard\RouteGuard::class => [
     *         \Prooph\ServiceBus\Container\Plugin\Guard\RouteGuardFactory::class,
     *         'exposeMessageName'
     *     ]
     * ];
     * </code>
     *
     * @param string $name
     * @param array $arguments
     * @return \Prooph\ServiceBus\Plugin\Guard\RouteGuard
     * @throws InvalidArgumentException
     */
    public static function __callStatic($name, array $arguments)
    {
        if (!isset($arguments[0]) || !$arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new static(true))->__invoke($arguments[0]);
    }

    /**
     * @param ContainerInterface $container
     * @return RouteGuard
     */
    public function __invoke(ContainerInterface $container)
    {
        $authorizationService = $container->get(AuthorizationService::class);

        return new RouteGuard($authorizationService, $this->exposeEventMessageName);
    }
}
