PSB Plugins
===========

[Back to documentation](../README.md#documentation)

Plugins expand a message bus with additional functionality.
PSB ships with a list of useful plugins that can be mixed and matched with your own implementations:

# Routers

## Prooph\ServiceBus\Plugin\Router\CommandRouter

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

## Prooph\ServiceBus\Plugin\Router\QueryRouter

Use the QueryRouter to provide a list of queries (identified by their names) and their responsible finders.

The QueryRouter share the same base class with the CommandRouter so its interface looks exactly the same.


## Prooph\ServiceBus\Plugin\Router\EventRouter

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

## Prooph\ServiceBus\Plugin\Router\RegexRouter

The RegexRouter works with regular expressions to determine handlers for messages. It can be used together with a CommandBus, a QueryBus and
an EventBus but for the latter it behaves a bit different. When routing a command or query the RegexRouter makes sure that only one pattern matches.
If more than one pattern matches it throws a `Prooph\ServiceBus\Exception\RuntimeException`. On the other hand when routing
an event each time a pattern matches the corresponding listener is added to the list of listeners.

```php
//You can provide the pattern list as an associative array in the constructor ...
$router = new RegexRouter(array('/^My\.Command\.Buy.*/' => new BuyArticleHandler()));

//... or using the programmatic api
$router->route('/^My\.Command\.Register.*/')->to(new RegisterUserHandler());

//Add the router to a CommandBus
$commandBus->utilize($router);

//When routing an event you can provide a list of listeners for each pattern ...
$router = new RegexRouter(array('/^My\.Event\.Article.*/' => array(new OrderCartUpdater(), new InventoryUpdater())));

//... or using the programmatic api
$router->route('/^My\.Event\.Article.*/')->to(new OrderCartUpdater());

//The RegexRouter does not provide a andTo method like the EventRouter.
//You need to call route again for the same pattern,
//otherwise the router throws an exception
$router->route('/^My\.Event\.Article.*/')->to(new InventoryUpdater());

//Add the router to an EventBus
$eventBus->utilize($router);
```

# Invoke Strategies

An invoke strategy knows how a message handler can be invoked. You can register many invoke strategies at once depending on
how many different handler types you are using. The best way is to choose a convention and go with it. PSB ships with the invoke strategies
listed below. If your favorite convention is not there you can easily write your own invoke strategy
by extending the [AbstractInvokeStrategy](../src/Prooph/ServiceBus/Plugin/InvokeStrategy/AbstractInvokeStrategy.php) and implementing the
`canInvoke` and `invoke` methods.

## Available Strategies

- `HandleCommandStrategy`: Is responsible for invoking a `handle` method of a command handler. Forces the rule that a command handler should only be responsible for handling one specific command.
- `OnEventStrategy`: Prefixes the short class name of an event with `on`. A listener should
have a public method named this way: OrderCartUpdater::onArticleWasBought.
- `FinderInvokeStrategy`: This strategy is responsible for invoking finders. It either looks for a finder method named like the short class name of the query.

Note: When a message bus detects that the message handler is callable invoke strategies are skipped and the message handler is directly invoked by the message bus.

# ServiceLocatorPlugin

This plugin uses a Interop\Container\ContainerInterface implementation to message handlers.
The following example uses a ZF2 ServiceManager as a service locator and illustrates how it can be used together with a command bus:

```php
use Zend\ServiceManager\ServiceManager;

//We tell the ServiceManager that it should provide an instance of My\Command\DoSomethingHandler
//when we request it with the alias My.Command.DoSomethingHandler
$serviceManager = new ServiceManager(new Config([
    'invokables' => [
        'My.Command.DoSomethingHandler' => 'My\Command\DoSomethingHandler'
    ]
]));

//The ZF2\ServiceManager implements Interop\Container\ContainerInterface since v2.6
$commandBus->utilize(new ServiceLocatorPlugin($serviceManager);

$router = new CommandRouter();

//In the routing map we use the alias of the command handler
$router->route('My.Command.DoSomething')->to('My.Command.DoSomethingHandler');

$commandBus->utilize($router);
```

With this technique you can configure the routing for all your messages without the need to create all the message handlers
on every request. Only the responsible message handler are lazy loaded by the service locator plugin.