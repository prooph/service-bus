PSB Overview
============

[Back to documentation](../README.md#documentation)

ProophServiceBus ships with two bus implementations namely a CommandBus and an EventBus. Both buses behave very similar but have
an important difference. The CommandBus is designed to dispatch a message to only one handler. The EventBus on the other site
is able to dispatch a message to multiple handlers. Both buses provide an event-driven dispatch process to give plugins
the possibility to hook into this process and manipulate it.

# Messaging

The goal of a message dispatch process is to locate and invoke an appropriate message handler. This is
true for commands and events (both are messages and only differ in their intention). The message handler itself is hidden
behind a bus. A sender don't know anything about the handler. It creates a message and triggers the
dispatch process on a message bus. The bus is responsible for delivering the message to it's handler even if the message handler is
part of an external system that can only be accessed via a remote interface. Messaging means fire and forget. The sender gets no
response when it triggers the dispatch except an error occurs during the dispatch process. In this case the message bus throws an exception.

# Synchronous Versus Asynchronous Processing

PSB provides both possibilities behind a unified interface.
Remember the statement "Messaging means fire and forget".
The message sender never knows if the message will be processed synchronous or asynchronous. It depends on the bus
configuration and/or the used plugins. A message can directly be routed to it's handler. In this case we talk about synchronous
message processing. If the receiver of the message is a [Prooph\ServiceBus\Message\MessageDispatcherInterface](message_dispatcher.md)
the message is normally processed asynchronously.
