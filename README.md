PHPServiceBus
===============

PHP 5.4+ Enterprise Service Bus Implementation

[![Build Status](https://travis-ci.org/codeliner/php-service-bus.png?branch=master)](https://travis-ci.org/codeliner/php-service-bus)

This library is heavily inspired by [malocher/cqrs-esb](https://github.com/malocher/cqrs-esb), [beberlei/litecqrs-php](https://github.com/beberlei/litecqrs-php) and [szjani/mf4php](https://github.com/szjani/mf4php).

Why another CQRS/Messaging library?
-----------------------------------

The goal of PHPServiceBus is to provide a powerful CQRS layer on top of different messaging/worker tools like [php-resque
](https://github.com/chrisboulton/php-resque), [RabbitMQ](https://www.rabbitmq.com/), [Pheanstalk](https://github.com/pda/pheanstalk) or RESTful Messaging API.
It is designed with flexibility in mind. Almost all components can be extended with custom behavior. But flexibility has it's price. The structure of PHPServiceBus
is more complex than of the other libraries. If you just want to get familiar with CQRS and/or messaging one of the above libs is maybe a better choice.

IoC and EDA
-----------
Like mentioned above, each part of PHPServiceBus can be extended or exchanged. The library uses the [ServiceManager](http://framework.zend.com/manual/2.0/en/modules/zend.service-manager.quick-start.html) and [EventManager](http://framework.zend.com/manual/2.0/en/modules/zend.event-manager.event-manager.html) implementations
of the ZendFramework 2. Specific managers and factories construct the various components used to send commands, publish events and receive them. All important actions trigger *.pre and *.post events to provide event-driven capabilities.
But let's explore it step by step.

Installation
------------
You can install PHPServiceBus via composer by adding `"codeliner/php-service-bus": "dev-master"` as requirement to your composer.json.

Quick Start
-----------
The simplest way to get started is to configure a local in memory service bus. In PHPServiceBus this would not be simple enough to use it as example in a quick start,
but PHPServiceBus ships with an Initializer, that do the hard work for us.

```php
use Codeliner\ServiceBus\Example\Command\EchoText;
use Codeliner\ServiceBus\Initializer\LocalSynchronousInitializer;
use Codeliner\ServiceBus\Service\ServiceBusManager;

//The ServiceBusManager is the central class, that manages the complete service bus environment
$serviceBusManager = new ServiceBusManager();

//We use an Initializer to configure a local in memory service bus
$localEnvironmentInitializer = new LocalSynchronousInitializer();

//Register a callback as CommandHandler for the EchoText command
$localEnvironmentInitializer->setCommandHandler(
    'Codeliner\ServiceBus\Example\Command\EchoText',
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

