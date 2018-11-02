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

namespace Prooph\ServiceBus\Container\Plugin\Guard;

use Prooph\ServiceBus\Exception\InvalidArgumentException;
use Prooph\ServiceBus\Plugin\Guard\AuthorizationService;
use Prooph\ServiceBus\Plugin\Guard\FinalizeGuard;
use Psr\Container\ContainerInterface;

class FinalizeGuardFactory
{
    /**
     * @var bool
     */
    private $exposeEventMessageName;

    public function __construct(bool $exposeEventMessageName = false)
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
     *     \Prooph\ServiceBus\Plugin\Guard\FinalizeGuard::class => [
     *         \Prooph\ServiceBus\Container\Plugin\Guard\FinalizeGuardFactory::class,
     *         'exposeMessageName'
     *     ]
     * ];
     * </code>
     *
     * @throws InvalidArgumentException
     */
    public static function __callStatic(string $name, array $arguments): FinalizeGuard
    {
        if (! isset($arguments[0]) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                \sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new static(true))->__invoke($arguments[0]);
    }

    public function __invoke(ContainerInterface $container): FinalizeGuard
    {
        $authorizationService = $container->get(AuthorizationService::class);

        return new FinalizeGuard($authorizationService, $this->exposeEventMessageName);
    }
}
