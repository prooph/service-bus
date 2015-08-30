Async Message Producer
======================

[Back to documentation](../README.md#documentation)

# Usage

Messaging becomes really interesting when you process your messages asynchronous. For example push your messages on a database queue,
set up a cron job to periodically check the queue for new messages and process them. The bus implementations of PSB can
hide such an asynchronous workflow behind a unified interface. You can start with synchronous message dispatching by
routing your messages directly to message handlers and if you later want to improve response times you can switch to
async processing on a per message basis by routing the appropriate messages to a [Async\MessageProducer](../src/Async/MessageProducer.php) listed below.

# Available MessageProducer

- [PhpResqueProducer](https://github.com/prooph/psb-php-resque-producer): Perfect choice for async
  command processing using a ultra fast redis queue
- [BernardProducer](https://github.com/prooph/psb-bernard-producer): Queue multi-backend providing different
  drivers like Doctrine DBAL and Predis (see http://bernardphp.com for a complete list of drivers)
- [GuzzleHttpProducer](https://github.com/prooph/psb-http-producer): Send messages to a remote system using
  HTTP

# QueryBus

A async message producer for the QueryBus needs to provide a response by resolving the handed over deferred.
In a messaging system based on RabbitMQ for example you can make use of a callback queue feature.
HTTP APIs provide responses naturally.
So these are both good candidates to use for remote querying.
