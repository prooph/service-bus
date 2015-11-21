# PSB Overview

prooph/service-bus acts as a messaging facade. It operates on application layer and shields your domain model.
In addition we also provide so called "message producers" which connect prooph/service-bus
with messaging infrastructure on system and network level.

Three different message bus implementations are available.

## CommandBus

The CommandBus is designed to dispatch a message to only one handler or message producer. It does not return a result.
Sending a command means *fire and forget* and enforces the *Tell-Don't-Ask* principle.

## EventBus

The EventBus is able to dispatch a message to `n` listeners. Each listener can be a message handler or message producer.
Like commands the EventBus doesn't return anything.

## QueryBus

The QueryBus also dispatches a message to only one finder (special query message handler)
but it returns a `React\Promise\Promise`. The QueryBus hands over the query message to a finder but
also a `React\Promise\Deferred` which needs to be resolved by the finder.
We use promises to allow finders to handle queries asynchronous for example using `curl_multi_exec`.

## Message Objects

You are free to use your own message objects (or even primitive types if you want). All message buses are smart enough to handle them.
If you need custom logic to handle your messages check out the list of available message bus plugins or write your own bus plugin.
It is pretty straight forward.

# Synchronous Versus Asynchronous Processing

PSB provides both possibilities behind a unified interface.
Remember the statement "Messaging means fire and forget".
The callee never knows if the message is processed synchronous or asynchronous.
A message can directly be routed to it's handler. In this case we talk about synchronous
message processing. If the message is routed to message producer it is normally processed asynchronous.
