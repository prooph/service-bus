The CommandBus
==============

# Usage

When you want to apply [CQRS](http://cqrs.files.wordpress.com/2010/11/cqrs_documents.pdf) you can use
the CommandBus as a thin layer between your application controllers and your write model.
The advantage of using a CommandBus instead of calling command handlers directly is, that you can change your model without effecting
the application logic. You can work with command versions to dispatch a newer version to a new command handler and older
versions to old command handlers. Your model can support different versions at the same time which makes migrations a lot easier.
The CommandBus can also act as a facade for remote systems. If you want to
trigger an action in an external system you can configure the CommandBus to forward the command to that system. Your controller knows
nothing about the other. With that in mind you can easily test your application logic. Simply replace the connection to the
external system in your test environment with a connection to a mocked command handler simulating the behaviour.

# API

```php
class CommandBus implements \Zend\EventManager\EventManagerAwareInterface
{
    /**
     * @param \Zend\EventManager\ListenerAggregateInterface|\Zend\Log\LoggerInterface $plugin
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function utilize($plugin);

    /**
     * @param \Zend\EventManager\ListenerAggregateInterface|\Zend\Log\LoggerInterface $plugin
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
     * @param mixed $command
     * @throws Exception\CommandDispatchException
     * @return bool
     */
    public function dispatch($command);
}
```

The public api of the CommandBus is very simple. Four of the five methods deal with adding or removing plugins and the last
one triggers the dispatch process of the given command.

# Event-Driven Dispatch

The command dispatch is an event-driven process provided by [Zend\EventManager](http://framework.zend.com/manual/2.0/en/modules/zend.event-manager.event-manager.html).
When a command is passed to the CommandBus via `CommandBus::dispatch` a new [CommandDispatch](command_dispatch.md) process is created by the CommandBus and populated with the given command.
Then the CommandBus triggers a chain of events. Listeners can listen on the events. They always get the CommandDispatch as the only argument and they can
modify it to help the CommandBus finish the process. A CommandBus without any registered plugins is useless and will throw an exception cause
by default the CommandBus do not know which command handler is responsible for the command.
Following events are triggered in the listed order:

- `initialize`: This event is triggered right after CommandBus::dispatch($command) is invoked. At this time the CommandDispatch only contains the command.
- `detect-message-name` (optional): Before a command handler can be located, the CommandBus needs to know how the command is named. Their are two
possibilities to provide the information. The command can implement the [Prooph\ServiceBus\Message\MessageNameProvider](../src/Prooph/ServiceBus/Message/MessageNameProvider.php) interface.
In this case the CommandBus picks the command name directly from the command and inject it manually in the CommandDispatch. The `detect-message-name` event is not triggered. If the command
does not implement the interface the `detect-message-name` event is triggered and a plugin needs to inject the name using `CommandDispatch::setCommandName`.
- `route`: During the `route` event a plugin should provide the responsible command handler either in form of a ready to use object or callable or as a string
representing an alias of the command handler that can be used by a DIC to locate an instance. The plugin should provide the handler by using
`CommandDispatch::setCommandHandler`.
- `locate-handler` (optional): After routing the CommandBus checks if the command handler was provided as a string. If so it triggers the
`locate-handler` event. This is the latest time to provide an object or callable as command handler. If no plugin was able to provide one the CommandBus throws an exception.
- `invoke-handler`: Having the command handler in place it's time to invoke it with the command. The CommandBus always triggers the event and performs no default action even if the
command handler is a callable.
- `handle-error`: If at any time a plugin or the CommandBus itself throws an exception it is caught and passed to the CommandDispatch. The normal event chain breaks and a
`handle-error` event is triggered instead. Listeners can access the exception by calling `CommandDispatch::getException`. When all listeners are informed about the error
the CommandBus throws a Prooph\ServiceBus\Exception\CommandDispatchException to inform the outside world about the error.
- `finalize`: This event is always triggered at the end of the process no matter if the process was successful or an exception was thrown. It is the ideal place to
attach a monitoring plugin.

# Plugins

Plugins can be simple callables (use the methods `on` and `off` to attach/detach them), implementations of the
Zend\EventManager\ListenerAggregateInterface (use the methods `ùtilize` and `deactivate` to attach/detach them) or an instance of
Zend\Log\LoggerInterface (also use methods `ùtilize` and `deactivate` to attach/detach it).
The signature of a plugin method/callable that listens on a CommandDispatch event is:

```php
function (\Prooph\ServiceBus\Process\CommandDispatch $commandDispatch) {};
```

Check the list of available [plugins](plugins.md) shipped with ProophServiceBus. If they don't meet your needs don't hesitate to write your
own plugins. It is really straight forward.

# Logging

If you add a Zend\Log\LoggerInterface as a plugin it is passed to the CommandDispatch and available during the dispatch so the
listener plugins can log their activities. If no logger is provided the CommandDispatch uses a /dev/null logger. Thus plugins can
invoke the logger without the need to consider if it is really available or not.



