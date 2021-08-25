# Prooph Service Bus

PHP 7.1+ lightweight message bus supporting CQRS and Micro Services

[![Build Status](https://travis-ci.com/prooph/service-bus.png?branch=master)](https://travis-ci.com/prooph/service-bus)
[![Coverage Status](https://coveralls.io/repos/prooph/service-bus/badge.svg?branch=master&service=github)](https://coveralls.io/github/prooph/service-bus?branch=master)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/prooph/improoph)

## Important

This library will receive support until December 31, 2019 and will then be deprecated.

For further information see the official announcement here: [https://www.sasaprolic.com/2018/08/the-future-of-prooph-components.html](https://www.sasaprolic.com/2018/08/the-future-of-prooph-components.html)

## Messaging API

prooph/service-bus is a lightweight messaging facade.
It allows you to define the API of your model with the help of messages.

1. **Command** messages describe actions your model can handle.
2. **Event** messages describe things that happened while your model handled a command.
3. **Query** messages describe available information that can be fetched from your (read) model.

prooph/service-bus shields your model. Data input and output ports become irrelevant and no longer influence business logic.
We're looking at you Hexagonal Architecture.

prooph/service-bus decouples your model from any framework. You can use a
web framework like Zend, Symfony, Laravel and co. to handle http requests and pass them via prooph/service-bus to your model
but you can also receive the same messages via CLI or from a messaging system like RabbitMQ or Beanstalkd.

It is also a perfect fit for microservices architecture as it provides an abstraction layer for message-based inter-service communication.

![prooph_architecture](https://raw.githubusercontent.com/prooph/proophessor/master/docs/book/img/prooph_overview.png)

## Installation

You can install prooph/service-bus via composer by running `composer require prooph/service-bus`, which will install the latest version as requirement to your composer.json.

## Quick Start

```php
<?php

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Example\Command\EchoText;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;

$commandBus = new CommandBus();

$router = new CommandRouter();

//Register a callback as CommandHandler for the EchoText command
$router->route('Prooph\ServiceBus\Example\Command\EchoText')
    ->to(function (EchoText $aCommand): void {
        echo $aCommand->getText();
    });

//Expand command bus with the router plugin
$router->attachToMessageBus($commandBus);

//We create a new Command
$echoText = new EchoText('It works');

//... and dispatch it
$commandBus->dispatch($echoText);

//Output should be: It works
```

## Live Coding Introduction

[![Prooph Service Bus v6](https://img.youtube.com/vi/6EcQjVSj3m4/0.jpg)](https://www.youtube.com/watch?v=6EcQjVSj3m4)

## Documentation

Documentation is [in the docs tree](docs/), and can be compiled using [bookdown](http://bookdown.io).

```console
$ php ./vendor/bin/bookdown docs/bookdown.json
$ php -S 0.0.0.0:8080 -t docs/html/
```

Then browse to [http://localhost:8080/](http://localhost:8080/)

## Support

- Ask questions on Stack Overflow tagged with [#prooph](https://stackoverflow.com/questions/tagged/prooph).
- File issues at [https://github.com/prooph/service-bus/issues](https://github.com/prooph/service-bus/issues).
- Say hello in the [prooph gitter](https://gitter.im/prooph/improoph) chat.

## Contribute

Please feel free to fork and extend existing or add new features and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

## License

Released under the New BSD License.
