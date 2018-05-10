# Prooph Service Bus Overview

prooph/service-bus acts as a messaging facade. It operates on the application layer and shields your domain model.
In addition we also provide so-called "message producers" which connect prooph/service-bus
with messaging infrastructure on the system and network level.

## Installation

```bash
composer require prooph/service-bus
```
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


## Messaging API

`prooph/service-bus` allows you to define the API of your model with the help of messages.

1. **Command** messages describe actions your model can handle.
2. **Event** messages describe things that happened while your model handled a command.
3. **Query** messages describe available information that can be fetched from your (read) model.

Three different message bus implementations are available, too.

## CommandBus

The CommandBus is designed to dispatch a message to exactly one handler or message producer. It does not return a result.
Sending a command means *fire and forget* and enforces the *Tell-Don't-Ask* principle.

## EventBus

The EventBus is able to dispatch a message to `n` listeners. Each listener can be a message handler or message producer.
Like commands the EventBus doesn't return anything.

## QueryBus

The QueryBus also dispatches a message to exactly one finder (special query message handler)
but it returns a `React\Promise\Promise`. The QueryBus hands the query message to a finder along with a `React\Promise\Deferred` which needs to be resolved by the finder.
We use promises to allow finders to handle queries asynchronous, for example using `curl_multi_exec`.

## Message Objects

You are free to use your own message objects (or even primitive types if you want). All message buses are smart enough to handle them.
If you need custom logic to handle your messages check out the list of available message bus plugins or write your own bus plugin.
It is pretty straight forward.

# Synchronous Versus Asynchronous Processing

Prooph Service Bus provides both possibilities behind a unified interface.
Remember the statement "Messaging means fire and forget".
The callee never knows if the message is processed synchronously or asynchronously.
A message can be directly routed to its handler. In this case, we talk about synchronous
message processing. If the message is routed to message producer it is normally processed asynchronously.
