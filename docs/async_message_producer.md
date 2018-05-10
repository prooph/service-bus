# Async Message Producer

Messaging becomes really interesting if you process your messages asynchronously. Prooph Service Bus can
hide such an asynchronous workflow behind a unified interface. You can start with synchronous message
dispatching by routing your messages directly to message handlers and if you later want to improve response
times you can switch to async processing on a per message basis by routing the appropriate messages to a
message producer which hands them over to a messaging system like RabbitMQ, ZeroMQ, Gearman, Beanstalkd or any
other queue.

## Available MessageProducer

- [BernardProducer](https://github.com/prooph/psb-bernard-producer): Queue multi-backend providing different
  drivers like Doctrine DBAL and Predis (see
  [https://github.com/bernardphp/bernard](https://github.com/bernardphp/bernard) for a complete list of drivers)
- [GuzzleHttpProducer](https://github.com/prooph/psb-http-producer): Send messages to a remote system using
  HTTP
- [ZeroMQProducer](https://github.com/prooph/psb-zeromq-producer): Async message handling using super fast
and simple to set up ZeroMQ
- [HumusAmqpProducer](https://github.com/prooph/humus-amqp-producer): Async handling using amqp protocol
(f.e. with RabbitMQ). This also includes JSON-RPC features.
- [EnqueueProducer](https://github.com/prooph/psb-enqueue-producer): Async handling using Enqueue MQ library.

## Usage

If you want to set up a bus that handles all messages asynchronously you can do so by attaching a
`Prooph\ServiceBus\Plugin\MessageProducerPlugin` initialized with your message producer of choice
to a message bus.

Let's look at a simple example using the `psb-zeromq-producer`

```php
//app bootstrap
$container = new Container;
$container['config'] = [
    'prooph' => [
        'zeromq_producer' => [
            'dsn' => 'tcp://127.0.0.1:5555', // ZMQ Server Address.
            'persistent_id' => 'example', // ZMQ Persistent ID to keep connections alive between requests.
            'rpc' => false, // Use as Query Bus.
        ]
    ]
];

$factory = \Prooph\ServiceBus\Message\ZeroMQ\Container\ZeroMQMessageProducerFactory;
$zmqProducer = $factory($container);

$commandBus = new \Prooph\ServiceBus\CommandBus();

$messageProducerForwarder = new \Prooph\ServiceBus\Plugin\MessageProducerPlugin($zmqProducer);

$messageProducerForwarder->attachToMessageBus($commandBus);

$echoText = new ExampleCommand('It works');
$commandBus->dispatch($echoText);
```

You can also route individual messages to message producers by using a message router plugin.

*Note: `Prooph\ServiceBus\Plugin\Router\RegexRouter` is a good choice
if you want to handle all messages of a specific namespace async.*

## Async Querying

An async message producer for the QueryBus needs to provide a response by resolving the input
`React\Promise\Deferred`. When using a messaging system like ZeroMQ for example you can make use of
request/response mode or RPC mode. HTTP APIs provide responses naturally. So these are both good
candidates to use for remote querying.
