The QueryBus
==============

[Back to documentation](../README.md#documentation)

# Usage


# API

```php
class QueryBus extends MessageBus
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
     * @param mixed $query
     * @return \React\Promise\Promise
     */
    public function dispatch($query);
}
```

The public API of the QueryBus is very simple. Four of the five methods deal with adding or removing plugins and the last
one triggers the dispatch process of the given query.

** Note: Only `dispatch` is implemented by the QueryBus the four other public methods are provided by the basic MessageBus implementation.

# Event-Driven Dispatch

The query dispatch is an event-driven process provided by a Prooph\Common\Event\ActionEventDispatcher.
When a query is passed to the QueryBus via `QueryBus::dispatch` a new [QueryDispatch](../src/Prooph/ServiceBus/Process/QueryDispatch.php) process is created by the QueryBus and populated with the given query.
Then the QueryBus triggers a chain of action events. Plugins can listen on these action events. They always get the QueryDispatch as the only argument and they can
modify it to help the QueryBus finish the process. A QueryDispatch without any registered plugins is useless and will throw an exception because
it does not know which finder is responsible for the query.
Following action events are triggered in the listed order:

- `initialize`: This action event is triggered right after QueryDispatch::dispatch($query) is invoked. At this time the QueryDispatch only contains the query.

- `detect-message-name` (optional): Before a finder can be located, the QueryBus needs to know how the query is named. Their are two
possibilities to provide the information. The query can implement the [Prooph\Common\Messaging\HasMessageName](https://github.com/prooph/common/blob/master/src/Messaging/HasMessageName.php) interface.
In this case the QueryBus picks the query name directly from the query and inject it manually in the QueryDispatch. The `detect-message-name` event is not triggered. If the query
does not implement the interface the `detect-message-name` event is triggered and a plugin needs to inject the name using `QueryDispatch::setQueryName`.

- `route`: During the `route` action event a plugin should provide the responsible finder either in form of a ready to use object or callable or as a string
representing an alias of the finder that can be used by a DIC to locate an instance. The plugin should provide the handler by using
`QueryDispatch::setFinder`.

- `locate-finder` (optional): After routing the query, the QueryBus checks if the finder was provided as a string. If so it triggers the
`locate-finder` event. This is the latest time to provide an object or callable as finder. If no plugin was able to provide one the QueryBus throws an exception.

- `invoke-finder`: Having the finder in place it's time to invoke it with the query and a [deferred](https://github.com/reactphp/promise/blob/master/src/Deferred.php).
The QueryBus always triggers the event. It performs no default action even if the finder is a callable.

- `handle-error`: If at any time a plugin or the QueryBus itself throws an exception it is caught and passed to the QueryDispatch. The normal action event chain breaks and a
`handle-error` event is triggered instead. Plugins can access the exception by calling `QueryDispatch::getException`.
A `handle-error` plugin or a `finalize` plugin can unset the exception by calling `QueryDispatch::setException(null)`.
When all plugins are informed about the error and no one has unset the exception the QueryBus rejects the promise with a Prooph\ServiceBus\Exception\QueryDispatchException to inform the producer about the error.

- `finalize`: This action event is always triggered at the end of the process no matter if the process was successful or the promise was rejected. It is the ideal place to
attach a monitoring plugin.

# Queries

A query can nearly be everything. PSB tries to get out of your way as much as it can. You are ask to use your own query implementation or you use the
default [Query](https://github.com/prooph/common/blob/master/src/Messaging/Query.php) class provided by prooph/common. It is a very good base class
and PSB ships with translator plugins to translate a Query into a remote message
that can be send to a remote interface. Check the [Remote Message Dispatcher](message_dispatcher.md) for more details.

# Plugins

Plugins can be simple callables (use the methods `on` and `off` to attach/detach them), implementations of the
\Prooph\Common\Event\ActionEventListenerAggregate (use the methods `utilize` and `deactivate` to attach/detach them) or an instance of
Psr\Log\LoggerInterface (also use methods `utilize` and `deactivate` to attach/detach it).
The signature of a plugin method/callable that listens on a CommandDispatch event is:

```php
function (\Prooph\ServiceBus\Process\QueryDispatch $queryDispatch) {};
```

Check the list of available [plugins](plugins.md) shipped with ProophServiceBus. If they don't meet your needs don't hesitate to write your
own plugins. It is really straight forward.

# Logging

If you add a Psr\Log\LoggerInterface as a plugin it is passed to the QueryDispatch and available during the dispatch so the
listener plugins can log their activities.





