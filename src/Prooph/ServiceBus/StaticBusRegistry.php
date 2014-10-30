<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.10.14 - 19:47
 */

namespace Prooph\ServiceBus;

/**
 * Class StaticBusRegistry
 *
 * The StaticBusRegistry can be used to set up globally available command and event bus.
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class StaticBusRegistry 
{
    /**
     * @var CommandBus
     */
    private static $commandBus;

    /**
     * @var EventBus
     */
    private static $eventBus;

    /**
     * @param \Prooph\ServiceBus\CommandBus $commandBus
     */
    public static function setCommandBus(CommandBus $commandBus)
    {
        self::$commandBus = $commandBus;
    }

    /**
     * @throws \RuntimeException
     * @return \Prooph\ServiceBus\CommandBus
     */
    public static function getCommandBus()
    {
        if (is_null(self::$commandBus)) {
            throw new \RuntimeException("Global command bus is not available. No instance registered!");
        }

        return self::$commandBus;
    }

    /**
     * @param \Prooph\ServiceBus\EventBus $eventBus
     */
    public static function setEventBus(EventBus $eventBus)
    {
        self::$eventBus = $eventBus;
    }

    /**
     * @throws \RuntimeException
     * @return \Prooph\ServiceBus\EventBus
     */
    public static function getEventBus()
    {
        if (is_null(self::$eventBus)) {
            throw new \RuntimeException("Global event bus is not available. No instance registered!");
        }

        return self::$eventBus;
    }

    public static function reset()
    {
        self::$commandBus = null;
        self::$eventBus   = null;
    }
}
 