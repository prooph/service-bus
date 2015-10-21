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

- `HandleCommandStrategy`: Prefixes the short class name of a command with `handle`.  A command handler should
have a public method named this way: AddProductHandler::handleAddProductToCart. If you prefer to have one command handler per command (recommended) then you don't need to attach the strategy but instead implement `CommandHandler::__invoke`. If you don't like `__invoke` you can also work with `CommandHandler::handle`. The `HandleCommandStrategy` checks if the handler has a single `handle` method if it cannot find an appropriate prefixed method name.
- `OnEventStrategy`: Prefixes the short class name of an event with `on`. A listener should
have a public method named this way: OrderCartUpdater::onArticleWasBought.
- `FinderInvokeStrategy`: This strategy is responsible for invoking finders. It looks for a finder method named like the short class name of the query.

Note: When a message bus detects that the message handler is callable invoke strategies are skipped and the message handler is directly invoked by the message bus.

# Guards

The service bus ships with a route guard and a finalize guard. You can use them to protect the command bus and the query bus.
For the command bus the route guard is sufficient. If the [AuthorizationService](../src/Prooph/ServiceBus/Plugin/Guard/AuthorizationService.php)
does not allow access to the command, an [UnauthorizedException](../src/Prooph/ServiceBus/Plugin/Guard/UnautorizedException.php) is thrown.
If you want to protect the query bus, you can also use the route guard, but in some situations, you want to deny access based on the result
of the query. In this case it's important to make checks on the query results.

The route guard passes the message to the [AuthorizationService](../src/Prooph/ServiceBus/Plugin/Guard/AuthorizationService.php) as context, so you can make assertions on it.

We also provide [service-bus-zfc-rbac-brdige](https://github.com/prooph/service-bus-zfc-rbac-bridge), a bridge to marry these guards with ZFC-Rbac.
You can also find some configuration examples in this repository. 

Note: If you use both, the route guard and the finalize guard on the query bus and you want to make assertions on the query result, it's important to return true, if the given context (query result) is null. Otherwise your assertions will always fail in the route phase, because the result is not yet known.

# ServiceLocatorPlugin

This plugin uses a `Interop\Container\ContainerInterface` implementation to lazy-load message handlers.
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

With this technique you can configure the routing for all your messages without the need to create all message handlers
on every request. Only the responsible message handlers are lazy loaded by the service locator plugin.

# MessageProducerPlugin

If you want to route all messages to an [async message producer](async_message_producer.md) you can attach
this plugin to a message bus. If it is attached to a command or query bus all messages will only be routed to
the message producer. If it is attached to an event bus the message producer
will be added to the list of event listeners.

```php
//Let's say the zeromq message producer is available as a service in a container
/** @var \Prooph\ServiceBus\Async\MessageProducer $zeromqProducer */
$zeromqProducer = $container->get('async_event_producer');

//We now only need to set up a message producer plugin and let the message bus use it.
$messageProducerPlugin = new \Prooph\ServiceBus\Plugin\MessageProducerPlugin($zeromqProducer);

$eventBus = new \Prooph\ServiceBus\EvenBus();

$eventBus->utilize($messageProducerPlugin);

//Each event will now be routed to the async message producer
$eventBus->dispatch($domainEvent);
```

# StringMessageInitializerPlugin

When you dispatch a string message you likely want to use it also as `message name`. This plugin does exactly that, just utilize the plugin and it will work.

```
$bus->utilize(new StringMessageInitializerPlugin);
$bus->dispatch('my.dispatch.message');
```
