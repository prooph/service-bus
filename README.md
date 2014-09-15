ProophServiceBus
===============

PHP Enterprise Service Bus Implementation supporting CQRS and DDD

THE LIB IS UNDER HEAVY REFACTRING! In v0.4.0 the system will be much simpler to use without losing it's power and flexibility. To achieve this we have to change the public interface. The central ServiceBusManager will no longer exist and you have to use dedicated CommandBus and EventBus implementations which can be extended by plugins/middleware.

Here's a little preview:


```php

//The dispatcher of a command message
//A command can only be dsipatched to one handler
$commandBus = new CommandBus();

//Commands and Events can nearly be everything: objects, arrays, strings .. 
$command = CreateUser::fromPayload(array('name' => 'John Doe'));

//The CommandRouter is one of the available plugins. And it's easy to write your own one.
//Plugins can route messages, translate them to other objects, invoke handlers and so on ...
$router = new CommandRouter();

//MessageHandler can be objects or callables ...
$router->route('CreateUser')->to(function(CreateUser $command) {/* your domain logic goes here ... */ });

$commandBus->utilize($router);

//.. just tell the bus how a handler can be invoked with a message
$callbackInvokeStrategy = new CallbackInvokeStrategy();

$coomandBus->utilize($callbackInvokeStrategy);

$commandBus->dispatch($command);

```

#You can't wait until v0.4.0 is relaesed? Here is the currently available version of the lib 

[![Build Status](https://travis-ci.org/prooph/service-bus.png?branch=master)](https://travis-ci.org/prooph/service-bus)


Why another CQRS/Messaging library?
-----------------------------------

The goal of ProophServiceBus is to provide a powerful CQRS layer on top of different messaging/worker tools like [PhpResque](https://github.com/chrisboulton/php-resque), [RabbitMQ](https://www.rabbitmq.com/), [Pheanstalk](https://github.com/pda/pheanstalk) or RESTful Messaging API.
It is designed with flexibility in mind. Almost all components can be extended with custom behavior and an event-driven system provides various possibilities to hook into the process and adjust it to meet your needs.
The library uses the [ServiceManager](http://framework.zend.com/manual/2.0/en/modules/zend.service-manager.quick-start.html) and [EventManager](http://framework.zend.com/manual/2.0/en/modules/zend.event-manager.event-manager.html) implementations of the ZendFramework 2 which offer flexible and easy to use solutions for IoC and EDA. But let's explore it step by step.

Features
--------

- [x] Unified interface for synchronous and asynchronous message dispatching.
- [x] Support for custom messages. Send whatever you want over the bus.
- [x] Configurable routing system.
- [x] Support for topic based dispatching.
- [x] Hook points to provide monitoring capability.
- [x] CQRS support
  - [x] One handler per command.
  - [x] Many handlers per event.
- [x] Easy to use for simple use cases but flexible enough to support indivdual requirements
- [x] [ProophEventStore](https://github.com/prooph/event-store) integration
- [x] [ZF2](https://github.com/prooph/ProophServiceBusModule) integration
- [ ] Exchangeable messaging system
  - [x] Simple message routing without any dispatcher (fast and easy to use, synchronous messaging)
  - [x] In memory message dispatcher (mock for a normally asynchronous message dispatcher)
  - [x] [PhpResque](https://github.com/chrisboulton/php-resque) message dispatcher (asynchronous)
  - [ ] [RabbitMQ](https://www.rabbitmq.com/) message dispatcher (asynchronous)
  - [ ] [Pheanstalk](https://github.com/pda/pheanstalk) message dispatcher (asynchronous)

Installation
------------

You can install ProophServiceBus via composer by adding `"prooph/service-bus": "~0.3"` as requirement to your composer.json.

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

The library is in an early state and a lot of features have to be implemented until we will release the first stable version. We apologize for the lack of documentation. We are working on it. With the 0.3.0 release ProophServiceBus has made a huge step forward and the main functionality has its final structure so we can start to document some of the key features. If you are looking for a more advanced use case then check out the [resque example](https://github.com/prooph/service-bus/wiki/Examples).

ZF2 Integration
---------------

[ProophServiceBusModule](https://github.com/prooph/ProophServiceBusModule) seamlessly integrates ProophServiceBus with a ZF2 application.


Contribute
----------

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and check your code using [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) against [PSR2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) using `./vendor/bin/phpcs -p --standard=PSR2 --ignore=vendor .`.

Acknowledgement
---------------

This library is heavily inspired by [malocher/cqrs-esb](https://github.com/malocher/cqrs-esb), [beberlei/litecqrs-php](https://github.com/beberlei/litecqrs-php) and [szjani/mf4php](https://github.com/szjani/mf4php).

