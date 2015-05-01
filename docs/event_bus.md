The EventBus
============

[Back to documentation](../README.md#documentation)

# Usage

When you want to apply [CQRS](http://cqrs.files.wordpress.com/2010/11/cqrs_documents.pdf) you need a way to inform the outside world
about events that happened in your write model be it your read model generators or other systems that rely on the information.
An EventBus is responsible for dispatching event messages to all interested listeners. If a listener is part of another system
the event may need to be send to a remote interface. The Prooph\ServiceBus\EventBus is capable to handle synchronous event
dispatching as well as asynchronous/remote event dispatching by using suitable plugins.

# API

```php
class EventBus extends MessageBus
{
    /**
     * @param \Prooph\Common\Event\ActionEventListenerAggregate|\Psr\Log\LoggerInterface $plugin
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function utilize($plugin);

    /**
     * @param \Prooph\Common\Event\ActionEventListenerAggregate|\Psr\Log\LoggerInterface $plugin
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function deactivate($plugin);

    /**
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return \Zend\Stdlib\CallbackHandler
     */
    public function on($eventName, $listener, $priority = 1);

    /**
     * @param \Zend\Stdlib\CallbackHandler $callbackHandler
     * @return bool
     */
    public function off(CallbackHandler $callbackHandler);

    /**
     * @param mixed $event
     * @throws Exception\EventDispatchException
     */
    public function dispatch($event);
}
```

The public api of the EventBus is very simple. Four of the five methods deal with adding or removing plugins and the last
one triggers the dispatch process of the given event.

** Note: Only `dispatch` is implemented by the EventBus the four other public methods are provided by the basic MessageBus implementation.

** Note: For the event-driven dispatch process the term `event` is used, too.  For example the first argument of the
method `EventBus::on` is called "eventName" or plugins should implement the \Prooph\Common\Event\ActionEventListenerAggregate. But these
namings have nothing to do with the event messages dispatched by the EventBus. The same wording is used to describe something that happens
now (triggering an action event) and something that happened in the past (the event message).

# Event-Driven Dispatch

The event dispatch is an event-driven process provided by a Prooph\Common\Event\ActionEventDispatcher.
When an event message is passed to the EventBus via `EventBus::dispatch` a new [EventDispatch](../src/Prooph/ServiceBus/Process/EventDispatch.php) process is created by the EventBus and populated with the given event message.
Then the EventBus triggers a chain of action events. Plugins can listen on the action events. They always get the EventDispatch as the only argument and they can
modify it to help the EventBus finish the process. An EventBus without any registered plugins is useless and will throw an exception because
it does not know which event message listener is interested in the event message.
Following action events are triggered in the listed order:

- `initialize`: This action event is triggered right after EventBus::dispatch($event) is invoked. At this time the EventDispatch only contains the event message.

- `detect-message-name` (optional): Before an event message listener can be located, the EventBus needs to know how the event message is named. Their are two
possibilities to provide the information. The event message can implement the [Prooph\Common\Messaging\HasMessageName](https://github.com/prooph/common/blob/master/src/Messaging/HasMessageName.php) interface.
In this case the EventBus picks the message name directly from the event message and inject it manually in the EventDispatch. The `detect-message-name` action event is not triggered. If the event message
does not implement the interface the `detect-message-name` action event is triggered and a plugin needs to inject the name using `EventDispatch::setEventName`.

- `route`: During the `route` action event one or more plugins should provide a list of interested event message listeners either in form of ready to use objects or callables or as strings
representing aliases of the event message listeners that can be used by a DIC to locate the listener instances. The plugins should provide and modify the list by using
`EventDispatch::setEventListeners` and `EventDispatch::addEventListener`.

- `locate-listener` (optional): After routing the event message, the EventBus loops over the list of interested event message listeners and checks for each of them
if the event message listener was provided as a string. If so it triggers a
`locate-listener` process event. This is the latest time to provide an object or callable as event message listener. The listener alias can be requested from the EventDispatch by
calling the method `EventDispatch::getCurrentEventListener` and the event message listener instance can be set via method `EventDispatch::setCurrentEventListener` If no plugin was able to provide an instance the EventBus throws an exception.

- `invoke-listener`: Foreach event message listener the EventBus triggers the `invoke-listener` action event. The EventBus always triggers the action event. It performs no default even if the
event message listener is a callable. Plugins can access the currently active event message listener by requesting it from the `EventDispatch::getCurrentEventListener` method.

- `handle-error`: If at any time a plugin or the EventBus itself throws an exception it is caught and passed to the EventDispatch. The normal action event chain breaks and a
`handle-error` action event is triggered instead. Plugins can access the exception by calling `EventDispatch::getException`.
A `handle-error` plugin or a `finalize` plugin can unset the exception by calling `EventDispatch::setException(null)`.
When all plugins are informed about the error and no one has unset the exception
the EventBus throws a Prooph\ServiceBus\Exception\EventDispatchException to inform the outside world about the error.

- `finalize`: This action event is always triggered at the end of the process no matter if the process was successful or an exception was thrown. It is the ideal place to
attach a monitoring plugin.

# Event Messages

An event message can nearly be everything. PSB tries to get out of your way as much as it can. You are ask to use your own event message implementation or you use the
default [DomainEvent](https://github.com/prooph/common/blob/master/src/Messaging/DomainEvent.php) class provided by prooph/common. It is a very good base class and PSB ships with translator plugins to translate an event message into a remote message
that can be send to a remote interface. Check the [Remote Message Dispatcher](message_dispatcher.md) for more details. However, you can provide
your own message translator plugin, a plugin that is capable of detecting the name of the event message and an invoke strategy that knows how to invoke
your event message listeners with the event message. Mix and match the plugins provided by PSB with your own ones to decouple your implementation from the PSB infrastructure.

# Plugins

Plugins can be simple callables (use the methods `on` and `off` to attach/detach them), implementations of the
\Prooph\Common\Event\ActionEventListenerAggregate (use the methods `utilize` and `deactivate` to attach/detach them) or an instance of
Psr\Log\LoggerInterface (also use methods `utilize` and `deactivate` to attach/detach it).
The signature of a plugin method/callable that listens on an EventDispatch is:

```php
function (\Prooph\ServiceBus\Process\EventDispatch $eventDispatch) {};
```

Check the list of available [plugins](plugins.md) shipped with ProophServiceBus. If they don't meet your needs don't hesitate to write your
own plugins. It is really straight forward.

# Logging

If you add a Psr\Log\LoggerInterface as a plugin, it is passed to the EventDispatch and available during the dispatch so the
plugins can log their activities.


