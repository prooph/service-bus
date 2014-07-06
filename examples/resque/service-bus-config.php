<?php
/**
 * This configuration is read by \Prooph\ServiceBus\Service\StaticServiceBusRegistry.
 */
return array(
    'prooph.service_bus' => array(
        //We can use the ServiceBusManager as IoC-Container and configure factories,
        //which are responsible for construct MessageHandler
        'service_bus_manager' => array(
            'factories' => array(
                'file_writer' => 'Prooph\ServiceBus\Example\Resque\FileWriterFactory'
            ),
        ),
        'command_bus' => array(
            'resque-sample-bus' => array(
                //Tell the bus which CommandHandler is responsible for a Command
                //The CommandHandlers can be aliased like shown here
                //The file_writer alias maps to a factory that constructs
                //a \Prooph\ServiceBus\Example\Resque\FileWriter
                'command_map' => array(
                    'Prooph\ServiceBus\Example\Resque\WriteLine' => 'file_writer'
                )
            )
        )
    ),
    //\Prooph\ServiceBus\Example\Resque\FileWriter configuration
    //is read by \Prooph\ServiceBus\Example\Resque\FileWriterFactory
    //to set up a FileWriter with the configured file
    'file_writer' => array(
        'file' => __DIR__ . '/dump.txt'
    ),
);