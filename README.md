PSB - ProophServiceBus
======================

PHP 5.5+ Lightweight Service Bus Facade supporting CQRS and Microservices

[![Build Status](https://travis-ci.org/prooph/service-bus.png?branch=master)](https://travis-ci.org/prooph/service-bus)

Message API
-----------

prooph/service-bus is a lightweight messaging facade sitting in front of your model. The idea is that the API of your model
is defined through messages.

1. Command messages describe the actions your model can handle.
2. Event messages describe things that happened while your model handled a command.
3. Query messages describe available information that can be fetched from your model.

prooph/service-bus shields your model. Data input and output ports become irrelevant and no longer influence the business logic.
I'm looking at you Hexagonal Architecture.

prooph/service-bus decouples your model from any framework except prooph/service-bus of course :-). You can use a
web framework like Zend, Symfony, Laravel and co. to handle http requests and pass them via prooph/service-bus to your model
but you can also receive the same messages via CLI or from a messaging infrastructure like RabbitMQ or Beanstalkd.

![psb_architecture](https://github.com/prooph/service-bus/blob/master/docs/img/psb_architecture.png)


Installation
------------

You can install prooph/service-bus via composer by adding `"prooph/service-bus": "~3.0"` as requirement to your composer.json.

Quick Start
-----------

The simplest way to get started is to set up one of the message buses provided by prooph/service-bus.

```php
<?php

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

//We create a new Command
$echoText = EchoText::fromString('It works');

//... and dispatch it
$commandBus->dispatch($echoText);

//Output should be: It works
```

Documentation
-------------

- [Overview](docs/service_bus_system.md)
- [CommandBus](docs/command_bus.md)
- [EventBus](docs/event_bus.md)
- [QueryBus](docs/query_bus.md)
- [Plugins](docs/plugins.md)
- [Asynchronous MessageDispatcher](docs/message_dispatcher.md)

# ZF2 Integration

[prooph/proophessor](https://github.com/prooph/proophessor) seamlessly integrates prooph/service-bus with a ZF2 application.
 

Support
-------

- Ask questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) google group.
- File issues at [https://github.com/prooph/service-bus/issues](https://github.com/prooph/service-bus/issues).


Contribute
----------

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.
