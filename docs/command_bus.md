The CommandBus
==============

[Back to documentation](../README.md#documentation)

# Usage

When you want to apply [CQRS](http://cqrs.files.wordpress.com/2010/11/cqrs_documents.pdf) the command bus is your best friend.
It takes an incoming command message and route it the responsible command handler.
The advantage of using a CommandBus instead of calling command handlers directly is, that you can change your model without effecting
the application logic. You can work with command versions to dispatch a newer version to a new command handler and older
versions to old command handlers. Your model can support different versions at the same time which makes migrations a lot easier.

# API

```php
class CommandBus extends MessageBus
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
     * @param mixed $command
     * @throws Exception\CommandDispatchException
     */
    public function dispatch($command);
}
```

The public API of the CommandBus is very simple. Four of the five methods deal with adding or removing plugins and the last
one triggers the dispatch process of the given command.

** Note: Only `dispatch` is implemented by the CommandBus the four other public methods are provided by the basic MessageBus implementation.

# Event-Driven Dispatch

The command dispatch is an event-driven process provided by a Prooph\Common\Event\ActionEventDispatcher.
When a command is passed to the CommandBus via `CommandBus::dispatch` a new [CommandDispatch](../src/Prooph/ServiceBus/Process/CommandDispatch.php) process is created by the CommandBus and populated with the given command.
Then the CommandBus triggers a chain of action events. Plugins can listen on these action events. They always get the CommandDispatch as the only argument and they can
modify it to help the CommandBus finish the process. A CommandBus without any registered plugins is useless and will throw an exception because
it does not know which command handler is responsible for the command.
Following action events are triggered in the listed order:

- `initialize`: This action event is triggered right after CommandBus::dispatch($command) is invoked. At this time the CommandDispatch only contains the command.

- `detect-message-name` (optional): Before a command handler can be located, the CommandBus needs to know how the command is named. Their are two
possibilities to provide the information. The command can implement the [Prooph\Common\Messaging\HasMessageName](https://github.com/prooph/common/blob/master/src/Messaging/HasMessageName.php) interface.
In this case the CommandBus picks the command name directly from the command and inject it manually in the CommandDispatch. The `detect-message-name` event is not triggered. If the command
does not implement the interface the `detect-message-name` event is triggered and a plugin needs to inject the name using `CommandDispatch::setCommandName`.

- `route`: During the `route` action event a plugin should provide the responsible command handler either in form of a ready to use object or callable or as a string
representing an alias of the command handler that can be used by a DIC to locate an instance. The plugin should provide the handler by using
`CommandDispatch::setCommandHandler`.

- `locate-handler` (optional): After routing the command, the CommandBus checks if the command handler was provided as a string. If so it triggers the
`locate-handler` event. This is the latest time to provide an object or callable as command handler. If no plugin was able to provide one the CommandBus throws an exception.

- `invoke-handler`: Having the command handler in place it's time to invoke it with the command. The CommandBus always triggers the event. It performs no default action even if the
command handler is a callable.

- `handle-error`: If at any time a plugin or the CommandBus itself throws an exception it is caught and passed to the CommandDispatch. The normal action event chain breaks and a
`handle-error` event is triggered instead. Plugins can access the exception by calling `CommandDispatch::getException`.
A `handle-error` plugin or a `finalize` plugin can unset the exception by calling `CommandDispatch::setException(null)`.
When all plugins are informed about the error and no one has unset the exception the CommandBus throws a Prooph\ServiceBus\Exception\CommandDispatchException to inform the outside world about the error.

- `finalize`: This action event is always triggered at the end of the process no matter if the process was successful or an exception was thrown. It is the ideal place to
attach a monitoring plugin.

# Commands

A command can nearly be everything. PSB tries to get out of your way as much as it can. You are ask to use your own command implementation or you use the
default [Command](https://github.com/prooph/common/blob/master/src/Messaging/Command.php) class provided by prooph/common. It is a very good base class
and PSB ships with translator plugins to translate a Command into a remote message
that can be send to a remote interface. Check the [Remote Message Dispatcher](message_dispatcher.md) for more details.

# Plugins

Plugins can be simple callables (use the methods `on` and `off` to attach/detach them), implementations of the
\Prooph\Common\Event\ActionEventListenerAggregate (use the methods `utilize` and `deactivate` to attach/detach them) or an instance of
Psr\Log\LoggerInterface (also use methods `utilize` and `deactivate` to attach/detach it).
The signature of a plugin method/callable that listens on a CommandDispatch event is:

```php
function (\Prooph\ServiceBus\Process\CommandDispatch $commandDispatch) {};
```

Check the list of available [plugins](plugins.md) shipped with ProophServiceBus. If they don't meet your needs don't hesitate to write your
own plugins. It is really straight forward.

# Logging

If you add a Psr\Log\LoggerInterface as a plugin it is passed to the CommandDispatch and available during the dispatch so the
listener plugins can log their activities.





