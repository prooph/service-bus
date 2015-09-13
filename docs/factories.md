# Interop\Container + Factories

[Back to documentation](../README.md#documentation)

Instead of providing a module, a bundle, a bridge or similar framework integration prooph/service-bus ships with
`container-aware factories`.

## Factory-Driven Message Bus Creation

The concept behind these factories is simple but powerful. It allows us to provide you with bootstrapping logic for
the message buses without the need to rely on a specific framework. However, the factories have two requirements.

### Requirements

1. Your Inversion of Control container must implement the [interop-container interface](https://github.com/container-interop/container-interop).
2. The application configuration should be registered with the service id `config` in the container.

*Note: Don't worry, if your environment doesn't provide the requirements. You can
always bootstrap a message bus by hand. Just look at the factories for inspiration in this case.*

## Customizing via Configuration

In the `config` folder you will find a [configuration skeleton](../config/prooph_service_bus.config.php)
and a [another configuration skeleton](../config/services.config.php) which contain the factories for the message guards.
The configuration is a simple PHP array flavored with some comments to help you understand the structure.

Now follow the simple steps below to integrate prooph/service-bus in your framework and/or application.

1. Merge the configuration skeleton into your application config either by hand or by using the mechanism of your framework.
2. Customize the configuration so that it meet your needs. The comments in the config file will tell you more.
3. (Only required if not done by your framework) Make your application config available as a service in the
Inversion of Control container. Use `config` as the service id (common id for application config).
4. Register the message buses as services in your IoC container and use the [factories](../src/Container) to create the [message buses](../src).
How you can register a message bus depends on your container. Some containers like [zend-servicemanager](https://github.com/zendframework/zend-servicemanager)
or [pimple-interop](https://github.com/moufmouf/pimple-interop) allow you to map a service id to an `invokable factory`.
If you use such an IoC container you are lucky. In this case you can use the prooph/service-bus factories as-is.
We recommend using `Prooph\ServiceBus\<CommandBus/EventBus/QueryBus::class` as service id.

*Note: If you're still unsure how to do it you might have a look at the [BusFactoriesTest](../tests/Container/BusFactoriesTest.php)*.
