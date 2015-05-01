RemoteMessageDispatcher
==============================

[Back to documentation](../README.md#documentation)

# Usage

Messaging becomes really interesting when you process your messages asynchronous. For example push your messages on a queue,
set up a cron job to periodically check the queue for new messages and process them. The bus implementations of PSB can
hide such an asynchronous workflow behind a unified interface. You can start with synchronous message dispatching by
routing your messages directly to message handlers and if you later want to improve response times you can switch to
async processing on message basis by routing the appropriate messages to a [RemoteMessageDispatcher](../src/Prooph/ServiceBus/Message/RemoteMessageDispatcher.php).

## Synchronous Dispatch
```php
//This example shows the simplified set up of a synchronous dispatch
$router = new EventRouter();

//We route the event directly to a listener
$router->route('SomethingDone')->to(new SomethingDoneListener());

$eventBus->utilize($router);

$eventBus->utilize(new OnInvokeStrategy());

$eventBus->dispatch(new SomethingDone());
```

Normally the ready to use EventBus is injected in a controller or service which then only uses the `EventBus::dispatch`
method without knowing something about the router set up. If you later change the routing like it is shown in the example below,
your controller or service logic can continue to work without any adaptions.

## Asynchronous Dispatch
```php
//This example shows the simplified set up of an asynchronous dispatch
//We dispatch an event in the example but the same technique can be used to
//dispatch a command asynchronously
$router = new EventRouter();

//We route the event to an async message dispatcher
//which implements Prooph\ServiceBus\Message\MessageDispatcherInterface
$router->route('SomethingDone')->to(new My\Async\MessageDispatcher());

$eventBus->utilize($router);

//The domain event needs to be translated to a Prooph\Common\Messaging\RemoteMessage
//The ForwardToRemoteMessageDispatcherStrategy checks if the listener of the event
//is an instance of Prooph\ServiceBus\Message\RemoteMessageDispatcher
//and translates the event with the help of a Prooph\ServiceBus\Message\ToRemoteMessageTranslator
$eventBus->utilize(new ForwardToRemoteMessageDispatcherStrategy(new ProophDomainMessageToRemoteMessageTranslator()));


//Now the event is dispatched to a RemoteMessageDispatcher instead of a listener
$eventBus->dispatch(new SomethingDone());

//Behind the scenes the remote message is translated to an array and pushed on a message queue
//There are various messaging tools available. We try to support the most important ones and
//continue to add more. Check the list below for available adapters. If your favorite adapter is
//not on the list you can easily implement it
//by implementing the Prooph\ServiceBus\Message\RemoteMessageDispatcher interface
//
//Now imagine that a worker has pulled the message array from the queue and want to process it
//It can simply create a remote message object from the array ...
$message = \Prooph\Common\Messaging\RemoteMessage::fromArray($messageArr);

//... set up another EventBus with a Prooph\ServiceBus\Message\FromRemoteMessageTranslator
//to translate the incoming message back to a domain event ...
$eventBus = new EventBus();

$eventBus->utilize(new FromRemoteMessageTranslator());

$router = new EventRouter();

//This time the event is dispatched to the interested listener
$router->route('SomethingDone')->to(new SomethingDoneListener());

$eventBus->utilize($router);

$eventBus->utilize(new OnInvokeStrategy());

$eventBus->dispatch($message);
```

# Available RemoteMessageDispatchers

- [InMemoryRemoteMessageDispatcher](../src/Prooph/ServiceBus/Message/InMemoryRemoteMessageDispatcher.php): useful for tests,
  you can replace your async dispatcher with this one 
- [PhpResqueMessageDispatcher](https://github.com/prooph/psb-php-resque-dispatcher): Perfect choice for async 
  command processing using a ultra fast redis queue
- [BernardMessageDispatcher](https://github.com/prooph/psb-bernard-dispatcher): Queue multi-backend providing different
  drivers like Doctrine DBAL and Predis (see http://bernardphp.com for a complete list of drivers)
- [GuzzleHttpMessageDispatcher](https://github.com/prooph/psb-http-dispatcher): Send messages to a remote system using
  HTTP
