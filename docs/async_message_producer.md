Async Message Producer
======================

[Back to documentation](../README.md#documentation)

# Async Messaging

Messaging becomes really interesting when you process your messages asynchronous. For example push your messages on a database queue,
set up a cron job to periodically check the queue for new messages and process them. The bus implementations of PSB can
hide such an asynchronous workflow behind a unified interface. You can start with synchronous message dispatching by
routing your messages directly to message handlers and if you later want to improve response times you can switch to
async processing on a per message basis by routing the appropriate messages to a [Async\MessageProducer](../src/Async/MessageProducer.php) listed below.

# Available MessageProducer

- [BernardProducer](https://github.com/prooph/psb-bernard-producer): Queue multi-backend providing different
  drivers like Doctrine DBAL and Predis (see http://bernardphp.com for a complete list of drivers)
- [GuzzleHttpProducer](https://github.com/prooph/psb-http-producer): Send messages to a remote system using
  HTTP
- [ZeromqProducer](https://github.com/prooph/psb-zeromq-producer): Async message handling using super fast and simple to
set up ZeroMQ

# Usage

If you want to set up a bus that handles all messages async you can do so by attaching a [MessageProducerPlugin](plugins.md#messageproducerplugin)
initialized with your message producer of choice to a message bus.

If you want to decide on a per message basis if the message should be handled async you can use a normal [message router](plugins.md#routers)
and configure your message producer of choice as message handler for the appropriate messages.

*Note: The [RegexRouter](plugins.md#proophservicebuspluginrouterregexrouter) is a good choice if you want to handle all messages of a specific namespace async.*

# QueryBus

A async message producer for the QueryBus needs to provide a response by resolving the handed over deferred.
In a messaging system based on ZeroMQ for example you can make use of request/response mode.
HTTP APIs provide responses naturally.
So these are both good candidates to use for remote querying.
