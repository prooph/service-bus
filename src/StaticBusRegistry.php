<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
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
 * The StaticBusRegistry can be used to set up globally available message buses.
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
     * @var QueryBus
     */
    private static $queryBus;

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
     * @param QueryBus $queryBus
     */
    public static function setQueryBus(QueryBus $queryBus)
    {
        self::$queryBus = $queryBus;
    }

    /**
     * @throws \RuntimeException
     * @return \Prooph\ServiceBus\QueryBus
     */
    public static function getQueryBus()
    {
        if (is_null(self::$queryBus)) {
            throw new \RuntimeException("Global query bus is not available. No instance registered!");
        }

        return self::$queryBus;
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
        self::$queryBus   = null;
    }
}
 