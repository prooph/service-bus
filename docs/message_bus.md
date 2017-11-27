# Message Buses

## Commanding

When you want to apply [CQRS](http://cqrs.files.wordpress.com/2010/11/cqrs_documents.pdf) the command bus is
your best friend.
It takes an incoming command message and routes it to the responsible command handler.
The advantage of using a CommandBus instead of calling command handlers directly is that you can change your model
without affecting the application logic. You can work with command versions to dispatch a newer version to a new
command handler and older versions to old command handlers. Your model can support different versions at the same
time which makes migrations a lot easier.
Another feature of a command bus could be automatic transaction handling.
And for distributed systems it is also interesting to push the command on a queue and handle it asynchronously.

## Eventing

When dividing your domain logic into modules or bounded contexts you need a way to inform the outside world
about events that happened in your model.
An EventBus is responsible for dispatching event messages to all interested listeners. If a listener is part of
another system the event may need to be sent to a remote interface.

## Querying

A system based on [Microservices](http://martinfowler.com/articles/microservices.html) requires a lightweight
communication channel.
The two most used protocols are HTTP request-response with resource API's and lightweight messaging. The latter
is supported by prooph/service-bus out-of-the-box but HTTP API's can be integrated too.
The QueryBus is responsible for routing a query message to a so-called finder. The query indicates that the
producer expects a response.
The finder's responsibility is to fetch data from a data source using the query parameters defined in the query
message. It is up to the finder if the data is fetched synchronous or asynchronous, so the QueryBus returns
a `React\Promise\Promise` to the callee.

## API

All three bus types extend the same base class `Prooph\ServiceBus\MessageBus` and therefore make use of an
event-driven message dispatch process. Take a look at the CommandBus API. It is the same for EventBus and
QueryBus except that the QueryBus returns a promise from `QueryBus::dispatch`.

```php
class CommandBus extends MessageBus
{
    public function attach(string $eventName, callable $listener, int $priority = 0): ListenerHandler

    public function detach(ListenerHandler $handler): void

    /**
     * @param mixed $command
     *
     * @throws CommandDispatchException
     */
    public function dispatch($command): void;
}
```

The public API of a message bus is very simple. You can attach and detach plugins which are simple event
listener aggregates and you can dispatch a message.

## Event-Driven Dispatch

Internally a prooph message bus uses an [event-driven process](https://github.com/prooph/common#actioneventemitter)
to dispatch messages.
This offers a lot of flexibility without the need to define interfaces for messages.
A message can be anything, even a string. prooph/service-bus doesn't care. But using some defaults will reduce
the number of required plugins and increase performance.

But first let's take a look at the internals of a message dispatch process and the differences between the
bus types.

### dispatch

This action event is triggered right after `MessageBus::dispatch($message)` is invoked.

The following default priorities are integrated:

```php
public const PRIORITY_INITIALIZE = 400000;
public const PRIORITY_DETECT_MESSAGE_NAME = 300000;
public const PRIORITY_ROUTE = 200000;
public const PRIORITY_LOCATE_HANDLER = 100000;
public const PRIORITY_INVOKE_HANDLER = 0;
```

#### initialize

At this time the action event only contains the `message`. You can attach any listeners for initialization.

#### detect-message-name

Before a message handler can be located, the message bus needs to know how the message is named. Their are two
possibilities to provide the information. The message can implement the
[Prooph\Common\Messaging\HasMessageName](https://github.com/prooph/common/blob/master/src/Messaging/HasMessageName.php)
interface.
In this case the message bus picks the name directly from the message and sets it as the param `message-name` in the
action event for use later. The `detect-message-name` event is not triggered. If the message
does not implement the interface the `detect-message-name` priority can be used to add a plugin to inject the
name using `ActionEvent::setParam('message-name', $messageName)`.
If no `message-name` was set by a listener, the message bus uses a fallback:
- FQCN of message in case of object
- message => message-name in case of string
- `gettype($message)` in all other cases

#### route

During the `route` phase a plugin (typically a router) should provide the responsible message handler either
in the form of a ready to use `callable`, an object or just a string.
The latter should be a service id that can be passed to a service locator to get an instance of the message
handler.
The message handler should be set as action event param `message-handler` (for CommandBus and QueryBus) or
`event-listeners` (for EventBus).

As you can see, the command and query bus work with a single message handler whereas the event bus works with
multiple listeners.
This is one of the most important differences. Only the event bus allows multiple message handlers per
message and therefore uses a slightly different dispatch process.

#### locate-handler

After routing the message, the message bus checks if the handler was provided as a string. This is the
last chance to provide an object or callable as message handler. If no plugin was able to provide one the
message bus throws an exception.

#### invoke-handler

With the message handler in place, it's time to invoke it with the message. `callable` message handlers
are invoked by the bus. However, the `invoke-handler` / `invoke-finder` events are always triggered.
At this stage all three bus types behave a bit different.

- CommandBus: invokes the handler with the command message. A `invoke-handler` event is triggered.
- QueryBus: much the same as the command bus but the message handler is invoked with the query message
and a `React\Promise\Deferred` that needs to be resolved by the message handler aka finder. The query bus
triggers a `invoke-finder` action event to indicate that a finder should be invoked and not a normal message
handler.
- EventBus: loops over all `event-listeners` and triggers the `locate-handler` and `invoke-handler` action
events for each message listener.

*Note:* The command and query bus have a mechanism to check if the command or query was handled. If not they
throw an exception.
The event bus does not have such a mechanism as having no listener for an event is a valid case.

### finalize

This action event is always triggered at the end of the process no matter if the process was successful
or an exception was thrown. It is the ideal place to attach a monitoring plugin.

If at any time a plugin or the message bus itself throws an exception it is caught and passed as param
`exception` to this action event. The normal action event chain breaks and a `finalize` event is triggered
instead. Plugins can then access the exception by getting it from the action event.
A `finalize` plugin can unset the exception by calling `ActionEvent::setParam("exception", null)`.
When all plugins are informed about the error and no one has unset the exception the message bus
throws a Prooph\ServiceBus\Exception\MessageDispatchException to inform the outside world about the error.

Note: The query bus has another additional priority `PRIORITY_PROMISE_REJECT` which is used to reject the promise
in case of an exception during the finalize event. If you want to remove the exception with a listener, you need
to add your listener with a higher priority than that.

## Migration from v5

### Events & Priorities

There are two things to consider when upgrading from v5.

1) The `handle-error` event is gone. If you want to have a plugin that tracks exceptions, you need to use the
`finalize` event and check for the existence of an exception. This can look like this:

```php
$commandBus->attach(
    CommandBus::EVENT_FINALIZE,
    function (ActionEvent $actionEvent) {
        if ($ex = $actionEvent->getParam(CommandBus::EVENT_PARAM_EXCEPTION)) {
            // do something
        }
    }
);
```

The event bus has a listener exception collection mode. This means that you can activate the mode and the event bus will
invoke all event listeners, catch possible exceptions and push them to an exception collection. If an exception is caught
the event bus will throw an `Prooph\ServiceBus\Exception\EventListenerException` at the end which contains all caught listener exceptions.

To enable the collection mode you can attach the plugin `Prooph\ServiceBus\Plugin\ListenerExceptionCollectionMode`.
Detach the plugin to deactivate the mode again.

2) There is a new `dispatch` event replacing all other previously existing events. It is controlled by
event priorities instead. So if your previous plugin looked like this:

```php
$commandBus->attach(
    CommandBus::EVENT_INVOKE_HANDLER,
    function (ActionEvent $actionEvent) {
        if ($ex = $actionEvent->getParam(CommandBus::EVENT_PARAM_EXCEPTION) {
            // do something
        }
    }
);
```

it now has to look like this:

```php
$commandBus->attach(
    CommandBus::EVENT_DISPATCH,
    function (ActionEvent $actionEvent) {
        if ($ex = $actionEvent->getParam(CommandBus::EVENT_PARAM_EXCEPTION) {
            // do something
        }
    },
    CommandBus::PRIORITY_INVOKE_HANDLER
);
```

3) Attaching to ActionEvents

Instead of calling:

```php
$commandBus
    ->getActionEventEmitter()
    ->attachListener(string $event, callable $listener, int $priority = 1): ListenerHandler;
```

It's more simple now:

```php
$commandBus->attach(string $event, callable $listener, int $priority = 1): ListenerHandler;
```

4) Plugins

Instead of implementing `Prooph\Common\Event\ActionEventListenerAggregate` a plugin now has to
implement `Prooph\ServiceBus\Plugin\Plugin`. The signature is:

```php
public function attachToMessageBus(MessageBus $messageBus): void;

public function detachFromMessageBus(MessageBus $messageBus): void;
```

### Further changes

#### FinderInvokeStrategy

Instead of having this:

```php
$finder->findQueryOne(QueryOne $query, Deferred $deferred = null): void;
```

you simply have this:

```php
$finder->find(QueryOne $query, Deferred $deferred = null): void;
```

If you want to go back to the old behaviour, you can do the following things:

a)

```php
class MyFinder
{
    public function find(Query $query, Deferred $deferred = null): void
    {
        if ($query instanceof QueryOne) {
            $this->findQueryOne($query, $deferred);
        } elseif ($query instanceof QueryTwo) {
            $this->findQueryTwo($query, $deferred);
        } else {
            throw new \InvalidArgumentException('Unknown query passed');
        }
    }
}
```

or b) Write a custom FinderInvokeStrategy.

#### HandleCommandStrategy

Same as for FinderInvokeStrategy, the handler is only expected have a `handle(Command $command): void` method.
If you need the old behaviour back, implement this in your handlers or write a custom plugin.

#### OnEventStrategy

Same as above: There handler is only expected to have a `onEvent(Event $message): void` method.
