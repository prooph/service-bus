PSB Plugins
===========

[Back to documentation](../README.md#documentation)

Plugins expand a message bus with additional functionality. The basic task of a message bus, be it a [CommandBus](command_bus.md) or [EventBus](event_bus.md),
is to dispatch a message. To achieve this goal the bus needs to collect some information about the message and perform
actions to ensure that a responsible message handler is invoked. Detailed information about the process can be found on the appropriate bus documentation pages.
Plugins hook into the dispatch process and provide the required information like the name of the message or a routing map and they also
prepare the message for invocation, locate the message handlers and invoke them.
PSB ships with a list of useful plugins that can be mixed and matched with your own implementations:

# Routers

## Prooph\ServiceBus\Router\CommandRouter

Use the CommandRouter to provide a list of commands (identified by their names) and their responsible command handlers.

```php
//You can provide the list as an associative array in the constructor ...
$router = new CommandRouter(array('My.Command.BuyArticle' => new BuyArticleHandler()));

//... or using the programmatic api
$router->route('My.Command.RegisterUser')->to(new RegisterUserHandler());

//Command handlers can be objects like shown above or everything that is callable (callbacks, callable arrays, etc.) ...
$router->route('My.Command.SendPaymentEmail')->to(array($mailer, "handleSendPaymentEmail"));

//... or a string that can be used by a DIC to locate the command handler instance
$router->route('My.Command.PayOrder')->to("payment_processor");

//Add the router to a CommandBus
$commandBus->utilize($router);
```

## Prooph\ServiceBus\Router\EventRouter

Use the EventRouter to provide a list of event messages (identified by their names) and all interested listeners per event message.

```php
//You can provide the list as an associative array in the constructor ...
$router = new EventRouter(array('My.Event.ArticleWasBought' => array(new OrderCartUpdater(), new InventoryUpdater())));

//... or using the programmatic api
$router->route('My.Event.ArticleWasBought')->to(new OrderCartUpdater())->andTo(new InventoryUpdater());

//Like command handlers, event message listeners can also be objects, callables or strings
$router->route('My.Event.OrderWasPayed')->to("delivery_processor");

//Add the router to an EventBus
$eventBus->utilize($router);
```

# Invoke Strategies

An invoke strategy knows how a message handler can be invoked. You can register many invoke strategies at once depending on
how many different handlers you are using. The best way is to choose a convention and go with it. PSB ships with the invoke strategies
listed below. If your favorite convention is not there you can easily write your own invoke strategy
by extending the [AbstractInvokeStrategy](../src/Prooph/ServiceBus/InvokeStrategy/AbstractInvokeStrategy.php) and implementing the
`canInvoke` and `invoke` methods.

## Available Strategies

- `CallbackStrategy`: Is responsible for invoking callable message handlers, can be used together with a CommandBus and EventBus
- `HandleCommandStrategy`: Determines the short class name of a command `My\Command\PayOrder becomes PayOrder` and prefix the short class name with
a `handle`. A method called this way needs to exist on a command handler: `OrderHandler::handlePayOrder`
- `OnEventStrategy`: Behave similar to the HandleCommandStrategy but prefixes the short class name of an event with `on`. A listener should
have a public method named this way: OrderCartUpdater::onArticleWasBought.
- `ForwardToMessageDispatcherStrategy`: This is a special invoke strategy that is capable of translating a command or event to
a [StandardMessage](../src/Prooph/ServiceBus/Message/StandardMessage.php) and invoke a [MessageDispatcher](message_dispatcher.md).
Add this strategy to a bus together with a [ToMessageTranslator](../src/Prooph/ServiceBus/Message/ToMessageTranslatorInterface.php) and
route a command or event to a MessageDispatcher to process the message async:

```php
$eventBus->utilize(new ForwardToMessageDispatcherStrategy(new ToMessageTranslator()));

$router = new EventRouter();

$router->route('SomethingDone')->to(new My\Async\MessageDispatcher());

$eventBus->utilize($router);

$eventBus->dispatch(new SomethingDone());
```

# FromMessageTranslator

The [FromMessageTranslator](../src/Prooph/ServiceBus/Message/FromMessageTranslator.php) plugin does the opposite of the `ForwardToMessageDispatcherStrategy`.
It listens on the `initialize` dispatch process event of a CommandBus or EventBus and if it detects an incoming [message](../src/Prooph/ServiceBus/Message/MessageInterface.php)
it translates the message to a [Command](../src/Prooph/ServiceBus/Command.php) or [Event](../src/Prooph/ServiceBus/Event.php) depending on the type
provided in the [MessageHeader](../src/Prooph/ServiceBus/Message/MessageHeaderInterface.php). A receiver of an asynchronous dispatched message, for example a worker of a
message queue, can pull a [message](../src/Prooph/ServiceBus/Message/MessageInterface.php) from the queue and forward it to a appropriate configured EventBus without additional work.

*Note: If the message name is an existing class it is used instead of the default implementation.
       But the constructor of the class should accept the same arguments as the default implementation does, otherwise you need to use your own message translator.

# Zf2ServiceLocatorProxy

PSB ships with out-of-the-box support for [Zend\ServiceManager](http://framework.zend.com/manual/2.0/en/modules/zend.service-manager.intro.html). You can use the
[Zf2ServiceLocatorProxy](../src/Prooph/ServiceBus/ServiceLocator/Zf2ServiceLocatorProxy.php) to delegate the localization of a message handler instance to a ServiceManager instance.

```php

//We tell the ServiceMaanger that it should provide an instance of My\Command\DoSomethingHandler
//when we request it with the alias My.Command.DoSomethingHandler
//The ServiceManager can create a new instance without further dependencies
$serviceManager = new ServiceManager(new Config([
    'invokables' => [
        'My.Command.DoSomethingHandler' => 'My\Command\DoSomethingHandler'
    ]
]));

$commandBus->utilize(new Zf2ServiceLocatorProxy($serviceManager));

$router = new CommandRouter();

//In the routing map we use the alias of the command handler
$router->route('My.Command.DoSomething')->to('My.Command.DoSomethingHandler');

$commandBus->utilize($router);
```

With this technique you can configure the routing for all your messages without the need to create all the message handlers
on every request. Only the responsible command handler or all interested event listeners (when dealing with event messages)
are lazy loaded by the ServiceManager. If you prefer to use another DIC then write your own proxy for it. Simply copy the
Zf2ServiceLocatorProxy and adapt it to handle your DIC.