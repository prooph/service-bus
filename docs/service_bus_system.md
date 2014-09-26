PSB Overview
============

[Back to documentation](../README.md#documentation)

ProophServiceBus ships with two bus implementations namely a CommandBus and an EventBus. Both buses behave very similar but have
an important difference. The CommandBus is designed to dispatch a message to only one handler. The EventBus on the other site
is able to dispatch a message to multiple handlers. Both buses provide an event-driven dispatch process to give plugins
the possibility to hook into this process and manipulate it.

# Messaging

The goal of a message dispatch process is to locate and invoke an appropriate message handler with the message. This is
true for commands and events (both are messages and only differ in their intention). The message handler itself is hidden
behind a bus. The message sender don't know anything about the handler. All it knows is that it has to create a message with
a set of defined properties or at least with the intention of the message in form of a message name and trigger the
dispatch process on a bus. The bus is responsible for delivering the message to it's handler even if the message handler is
part of another system and can only be reached via a remote interface. Messaging means fire and forget. The sender gets no
response when it triggers the dispatch.

# Synchronous Versus Asynchronous Processing

PSB provides both possibilities with the public api of the [CommandBus](command_bus.md) and [EventBus](event_bus.md) as a unified interface.
Remember the statement "Messaging means fire and forget".
The message sender never knows if the message will be processed synchronous or asynchronous. It depends on the bus
configuration (or the used plugins). A message can directly be routed to it's handler. In this case we talk about synchronous
message processing. If the receiver of the message is a [Prooph\ServiceBus\Message\MessageDispatcherInterface](message_dispatcher.md)
the message is normally processed asynchronously.
