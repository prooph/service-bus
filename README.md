ProophServiceBus
===============

PHP Enterprise Service Bus Implementation supporting CQRS and DDD

[![Build Status](https://travis-ci.org/prooph/service-bus.png?branch=master)](https://travis-ci.org/prooph/service-bus)


Why another CQRS/Messaging library?
-----------------------------------


The goal of ProophServiceBus is to provide a powerful CQRS layer on top of different messaging/worker tools like [PhpResque](https://github.com/chrisboulton/php-resque), [RabbitMQ](https://www.rabbitmq.com/), [Pheanstalk](https://github.com/pda/pheanstalk) or RESTful Messaging API.
It is designed with flexibility in mind. An event-driven system provides the possibility to add plugins or middleware. So you can easily hook into the process and adjust it to meet your needs.

Features
--------

- [x] Unified interface for synchronous and asynchronous message dispatching.
- [x] Support for custom messages. Send whatever you want over the buses.
- [x] Expendable core system.
- [x] CQRS support
  - [x] One handler per command.
  - [x] Many handlers per event.
- [x] Exchangeable messaging system
  - [x] Simple command and event routing without a message dispatcher (fast and easy to use, synchronous messaging)
  - [x] In memory message dispatcher (mock for a normally asynchronous message dispatcher)
  - [ ] [PhpResque](https://github.com/chrisboulton/php-resque) message dispatcher (asynchronous, currently not available, use a version < 0.4.0 if you need it)
  - [ ] [RabbitMQ](https://www.rabbitmq.com/) message dispatcher (asynchronous)
  - [ ] [Pheanstalk](https://github.com/pda/pheanstalk) message dispatcher (asynchronous)

Installation
------------

You can install ProophServiceBus via composer by adding `"prooph/service-bus": "~0.4"` as requirement to your composer.json.

Quick Start
-----------

The simplest way to get started is to set up a command or event bus with the default components provided by ProophServiceBus.
When you look at the different default plugins you should get an idea of how you can write your own plugins, if you need special behaviour.

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

We lack some documentation. When we've documented the important parts, we will release the first stable version.


Contribute
----------

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and check your code using [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) against [PSR2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) using `./vendor/bin/phpcs -p --standard=PSR2 --ignore=vendor .`.
