ProophServiceBus
===============

PHP Enterprise Service Bus Implementation supporting CQRS and DDD

[![Build Status](https://travis-ci.org/prooph/service-bus.png?branch=master)](https://travis-ci.org/prooph/service-bus)


Why another CQRS/Messaging library?
-----------------------------------

The goal of ProophServiceBus is to provide a powerful CQRS layer on top of different messaging/worker tools like [PhpResque](https://github.com/chrisboulton/php-resque), [RabbitMQ](https://www.rabbitmq.com/), [Pheanstalk](https://github.com/pda/pheanstalk) or RESTful Messaging API.
It is designed with flexibility in mind. Almost all components can be extended with custom behavior and an event-driven system provides various possibilities to hook into the process and adjust it to meet your needs.

IoC and EDA
-----------

Like mentioned above, each part of ProophServiceBus can be extended or exchanged. The library uses the [ServiceManager](http://framework.zend.com/manual/2.0/en/modules/zend.service-manager.quick-start.html) and [EventManager](http://framework.zend.com/manual/2.0/en/modules/zend.event-manager.event-manager.html) implementations
of the ZendFramework 2. Specific managers and factories construct the various components used to send commands, publish events and receive them.
But let's explore it step by step.

Installation
------------

You can install ProophServiceBus via composer by adding `"prooph/service-bus": "~0.3.0"` as requirement to your composer.json.

Quick Start
-----------

The simplest way to get started is to configure a command map
and use the routing mechanism of the ServiceBus to pass a command directly (synchronous) to a command handler.

```php
use Prooph\ServiceBus\Example\Command\EchoText;
use Prooph\ServiceBus\Service\ServiceBusConfiguration;
use Prooph\ServiceBus\Service\ServiceBusManager;

//The ServiceBus environment is set up by a special configuration class
$serviceBusConfig = new ServiceBusConfiguration();

//Register a callback as CommandHandler for the EchoText command
$serviceBusConfig->setCommandMap(array(
    'Prooph\ServiceBus\Example\Command\EchoText' => function (EchoText $aCommand) {
        echo $aCommand->getText();
    }
));

//The ServiceBusManager is the central class and manages the service bus environment
$serviceBusManager = new ServiceBusManager($serviceBusConfig);

//We create a new Command
//Assume that the EchoText command extends Prooph\ServiceBus\Command\AbstractCommand
$echoText = EchoText::fromPayload('It works');

//... and send it to a handler via routing system of the ServiceBus
$serviceBusManager->route($echoText);

//Output should be: It works
```

What's next?
------------

Read the [Wiki](https://github.com/prooph/php-service-bus/wiki)

Contribute
----------

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and check your code using [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) against [PSR2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) using `./vendor/bin/phpcs -p --standard=PSR2 --ignore=vendor .`.

Acknowledgement
---------------

This library is heavily inspired by [malocher/cqrs-esb](https://github.com/malocher/cqrs-esb), [beberlei/litecqrs-php](https://github.com/beberlei/litecqrs-php) and [szjani/mf4php](https://github.com/szjani/mf4php).

