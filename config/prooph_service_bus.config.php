<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 8/17/15 - 9:24 PM
 */
/**
 * This file contains default configuration for prooph/service-bus
 * It is meant to be used together with at least one of the container-aware factories
 * shipped with this package. Please refer to src/Factory for the factories and
 * register them in your Interop\Container\ContainerInterface of choice
 * Then make this config available as service id `config` within your container
 * (possibly merged into your application configuration)
 */
return [
    //vendor key to avoid merge conflicts with other packages when merged into application config
    'prooph' => [
        //component key to avoid merge conflicts with other prooph components when merged into application config
        'service_bus' => [
            //This section will be used by Prooph\ServiceBus\Factory\CommandBusFactory
            'command_bus' => [
                //You can add a list of container service ids
                //The factory will use these to get the plugins from the container
                'plugins' => [],
                'router' => [
                    //Map of message routes where the message name being the key and the command handler being the value.
                    //To lazy-load command handlers you can provide a service id instead.
                    //In this case the handler is pulled from the container using the provided handler service id
                    'routes' => [],
                    //Router defaults to Prooph\ServiceBus\Plugin\Router\CommandRouter
                    //Comment out the next line to use the RegexRouter instead
                    //'type' => \Prooph\ServiceBus\Plugin\Router\RegexRouter::class,
                ]
            ],
            //This section will be used by Prooph\ServiceBus\Factory\EventBusFactory
            'event_bus' => [
                //You can add a list of container service ids
                //The factory will use these to get the plugins from the container
                'plugins' => [],
                'router' => [
                    //Map of message routes where the message name being the key and the value being a list of event listeners.
                    //To lazy-load event listeners you can provide service ids instead.
                    //In this case each listener is pulled from the container using the provided listener service id
                    'routes' => [],
                    //Router defaults to Prooph\ServiceBus\Plugin\Router\EventRouter
                    //Comment out the next line to use the RegexRouter instead
                    //'type' => \Prooph\ServiceBus\Plugin\Router\RegexRouter::class,
                ]
            ],
            //This section will be used by Prooph\ServiceBus\Factory\QueryBusFactory
            'query_bus' => [
                //You can add a list of container service ids
                //The factory will use these to get the plugins from the container
                'plugins' => [],
                //Map of message routes where the message name being the key and the query handler being the value.
                //To lazy-load query handlers you can provide a service id instead.
                //In this case the handler is pulled from the container using the provided handler service id
                'router' => [
                    'routes' => [],
                    //Router defaults to Prooph\ServiceBus\Plugin\Router\QueryRouter
                    //Comment out the next line to use the RegexRouter instead
                    //'type' => \Prooph\ServiceBus\Plugin\Router\RegexRouter::class,
                ]
            ],
        ], //EO service_bus
    ], //EO prooph
];
