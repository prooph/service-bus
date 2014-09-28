PSB - ProophServiceBus
======================

PHP Enterprise Service Bus Facade supporting CQRS

[![Build Status](https://travis-ci.org/prooph/service-bus.png?branch=master)](https://travis-ci.org/prooph/service-bus)

About Prooph
------------

Prooph is the organisation behind [gingerframework](https://github.com/gingerframework/gingerframework) - a workflow framework written in PHP.
The founder and lead developer is [codeliner](https://github.com/codeliner). Prooph provides CQRS+ES infrastructure components for the gingerframework.
The components are split into 3 major libraries [ProophServiceBus](https://github.com/prooph/service-bus), [ProophEventSourcing](https://github.com/prooph/event-sourcing),
[ProophEventStore](https://github.com/prooph/event-store) and various minor libraries which add additional features or provide support for other frameworks.
The public APIs of the major components are stable. They are loosely coupled among each other and with the gingerframework, so you can mix and match them with
other libraries.

Why another CQRS/Messaging library?
-----------------------------------

The goal of ProophServiceBus is to provide a powerful CQRS layer on top of different messaging/worker tools like [PhpResque](https://github.com/chrisboulton/php-resque), [RabbitMQ](https://www.rabbitmq.com/), [Pheanstalk](https://github.com/pda/pheanstalk) or RESTful Messaging API.
It is designed with flexibility in mind. An event-driven system provides the possibility to add plugins for additional functionality. You can easily hook into the process and adjust it to meet your needs.

Installation
------------

You can install ProophServiceBus via composer by adding `"prooph/service-bus": "~1.0"` as requirement to your composer.json.

Quick Start
-----------

The simplest way to get started is to set up a command or event bus with the default components provided by ProophServiceBus.

```php
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Example\Command\EchoText;
use Prooph\ServiceBus\InvokeStrategy\CallbackStrategy;
use Prooph\ServiceBus\Router\CommandRouter;

$commandBus = new CommandBus();

$router = new CommandRouter();

//Register a callback as CommandHandler for the EchoText command
$router->route('Prooph\ServiceBus\Example\Command\EchoText')
    ->to(function (EchoText $aCommand) {
        echo $aCommand->getText();
    });

//Expand command bus with the router plugin
$commandBus->utilize($router);

//Expand command bus with the callback invoke strategy
$commandBus->utilize(new CallbackStrategy());

//Create a new Command
$echoText = EchoText::fromPayload('It works');

//... and dispatch it
$commandBus->dispatch($echoText);

//Output should be: It works
```

Documentation
-------------

- [Overview](docs/service_bus_system.md)
- [CommandBus](docs/command_bus.md)
- [EventBus](docs/event_bus.md)
- [Plugins](docs/plugins.md)
- [Asynchronous MessageDispatcher](docs/message_dispatcher.md)

Support
-------

- Ask questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) google group.
- File issues at [https://github.com/prooph/service-bus/issues](https://github.com/prooph/service-bus/issues).


Contribute
----------

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.