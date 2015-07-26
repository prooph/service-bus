PSB Overview
============

[Back to documentation](../README.md#documentation)

prooph/service-bus ships with three different bus implementations namely a CommandBus, a QueryBus and an EventBus. All buses behave similar but have
important differences.

- The CommandBus is designed to dispatch a message to only one handler.
- The EventBus on the other site is able to dispatch a message to multiple listeners.
- The QueryBus also dispatches a message to only one finder but it returns a [promise](https://github.com/reactphp/promise).

All buses provide an event-driven dispatch process to give plugins
the possibility to hook into the process and manipulate it.

# Messaging

The goal of a message dispatch process is to locate and invoke an appropriate message handler. This is
true for commands, queries and events (all are messages but differ in their intention). The message handler itself is hidden
behind a bus. A message producer don't know anything about the handler. It creates a message and triggers the
dispatch process on a message bus. The bus is responsible for delivering the message to it's handler even if the message handler is
part of an external system that can only be accessed via a remote interface.

For commands and events that means fire and forget. The producer gets no
response when it triggers the dispatch except an error occurs during the dispatch process.
In this case the message bus throws an exception.

When dispatching a query the message producer gets a promise back from the QueryBus. He also doesn't know if the
query is dispatched synchronous or asynchronous but he can attach to the `promise::then` method to receive the response
of the query.

## Message Objects

You are free to use your own message objects (or even primitive types if you want). All message buses are smart enough to handle them.
If you need custom logic to handle your messages check out the list of available [plugins](plugins.md) or write your own bus plugin.
It is pretty straight forward.

# Synchronous Versus Asynchronous Processing

PSB provides both possibilities behind a unified interface.
Remember the statement "Messaging means fire and forget".
The message producer never knows if the message is processed synchronous or asynchronous. It depends on the bus
configuration and/or the used plugins. A message can directly be routed to it's handler. In this case we talk about synchronous
message processing. If the handler of the message is a [queue producer](queue_producer.md)
the message is normally processed asynchronously.
