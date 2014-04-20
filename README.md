ProophServiceBus
===============

PHP 5.4+ Enterprise Service Bus Implementation

[![Build Status](https://travis-ci.org/prooph/php-service-bus.png?branch=master)](https://travis-ci.org/prooph/php-service-bus)

This library is heavily inspired by [malocher/cqrs-esb](https://github.com/malocher/cqrs-esb), [beberlei/litecqrs-php](https://github.com/beberlei/litecqrs-php) and [szjani/mf4php](https://github.com/szjani/mf4php).

Why another CQRS/Messaging library?
-----------------------------------

The goal of ProophServiceBus is to provide a powerful CQRS layer on top of different messaging/worker tools like [PhpResque](https://github.com/chrisboulton/php-resque), [RabbitMQ](https://www.rabbitmq.com/), [Pheanstalk](https://github.com/pda/pheanstalk) or RESTful Messaging API.
It is designed with flexibility in mind. Almost all components can be extended with custom behavior. But flexibility has it's price. The structure of ProophServiceBus
is more complex than of other libraries. If you just want to get familiar with CQRS and/or messaging, maybe another lib is a better choice.

IoC and EDA
-----------

Like mentioned above, each part of ProophServiceBus can be extended or exchanged. The library uses the [ServiceManager](http://framework.zend.com/manual/2.0/en/modules/zend.service-manager.quick-start.html) and [EventManager](http://framework.zend.com/manual/2.0/en/modules/zend.event-manager.event-manager.html) implementations
of the ZendFramework 2. Specific managers and factories construct the various components used to send commands, publish events and receive them. All important actions trigger *.pre and *.post events to provide event-driven capabilities.
But let's explore it step by step.

Installation
------------

You can install ProophServiceBus via composer by adding `"prooph/php-service-bus": "dev-master"` as requirement to your composer.json.

Quick Start
-----------

The simplest way to get started is to configure a local in memory service bus. In ProophServiceBus this would not be simple enough to use it as example in a quick start,
but ProophServiceBus ships with an Initializer, that do the hard work for us.

```php
use Prooph\ServiceBus\Example\Command\EchoText;
use Prooph\ServiceBus\Initializer\LocalSynchronousInitializer;
use Prooph\ServiceBus\Service\ServiceBusManager;

//The ServiceBusManager is the central class, that manages the service bus environment
$serviceBusManager = new ServiceBusManager();

//We use an Initializer to configure a local in memory service bus
$localEnvironmentInitializer = new LocalSynchronousInitializer();

//Register a callback as CommandHandler for the EchoText command
$localEnvironmentInitializer->setCommandHandler(
    'Prooph\ServiceBus\Example\Command\EchoText',
    function (EchoText $aCommand) {
        echo $aCommand->getText();
    }
);

//Register the Initializer at the event system of the ServiceBusManager
$serviceBusManager->events()->attachAggregate($localEnvironmentInitializer);

//Get the default CommandBus from ServiceBusManager
$commandBus = $serviceBusManager->getCommandBus();

//Create a new Command
$echoText = new EchoText('It works');

//... and send it to a handler via CommandBus
$commandBus->send($echoText);

//Output should be: It works
```

What's next?
------------

Read the [Wiki](https://github.com/prooph/php-service-bus/wiki)

Contribute
----------

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and check your code using [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) against [PSR2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) using `./vendor/bin/phpcs -p --standard=PSR2 --ignore=vendor .`.


