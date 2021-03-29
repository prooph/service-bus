<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2021 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Factory;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\Async\AsyncMessage;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Container\AbstractBusFactory;
use Prooph\ServiceBus\Container\CommandBusFactory;
use Prooph\ServiceBus\Container\EventBusFactory;
use Prooph\ServiceBus\Container\QueryBusFactory;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\InvalidArgumentException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\Plugin;
use Prooph\ServiceBus\Plugin\Router\RegexRouter;
use Prooph\ServiceBus\QueryBus;
use ProophTest\ServiceBus\Mock\NoopMessageProducer;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;

class BusFactoriesTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_bus_without_needing_a_application_config(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(false);
        $container->has(MessageFactory::class)->willReturn(false);

        $bus = $busFactory($container->reveal());

        $this->assertInstanceOf($busClass, $bus);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_bus_without_needing_prooph_config(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([]);
        $container->has(MessageFactory::class)->willReturn(false);

        $bus = $busFactory($container->reveal());

        $this->assertInstanceOf($busClass, $bus);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_new_bus_with_all_plugins_attached_using_a_container_and_configuration(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $firstPlugin = $this->prophesize(Plugin::class);
        $secondPlugin = $this->prophesize(Plugin::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'plugins' => [
                            'first_plugin_service_id',
                            'second_plugin_service_id',
                        ],
                    ],
                ],
            ],
        ]);

        $firstPlugin->attachToMessageBus(Argument::type(MessageBus::class))->shouldBeCalled();
        $secondPlugin->attachToMessageBus(Argument::type(MessageBus::class))->shouldBeCalled();

        $container->has('first_plugin_service_id')->willReturn(true);
        $container->get('first_plugin_service_id')->willReturn($firstPlugin->reveal());
        $container->has('second_plugin_service_id')->willReturn(true);
        $container->get('second_plugin_service_id')->willReturn($secondPlugin->reveal());

        $container->has(MessageFactory::class)->willReturn(false);

        $bus = $busFactory($container->reveal());

        $this->assertInstanceOf($busClass, $bus);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_throws_a_runtime_exception_if_plugin_is_not_registered_in_container(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $this->expectException(RuntimeException::class);

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'plugins' => [
                            'plugin_service_id',
                        ],
                    ],
                ],
            ],
        ]);

        $container->has('plugin_service_id')->willReturn(false);

        $container->has(MessageFactory::class)->willReturn(false);

        $busFactory($container->reveal());
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_bus_with_the_default_router_attached_if_routes_are_configured(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $message = $this->prophesize(Message::class);

        $message->messageName()->willReturn('test_message');
        $handlerWasCalled = false;

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'router' => [
                            'routes' => [
                                'test_message' => function (Message $message) use (&$handlerWasCalled): void {
                                    $handlerWasCalled = true;
                                },
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $container->has(MessageFactory::class)->willReturn(false);

        $bus = $busFactory($container->reveal());

        $bus->dispatch($message->reveal());

        $this->assertTrue($handlerWasCalled);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_bus_and_attaches_the_router_defined_via_configuration(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $message = $this->prophesize(Message::class);

        $message->messageName()->willReturn('test_message');
        $handlerWasCalled = false;

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'router' => [
                            'type' => RegexRouter::class,
                            'routes' => [
                                '/^test_./' => function (Message $message) use (&$handlerWasCalled): void {
                                    $handlerWasCalled = true;
                                },
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $container->has(MessageFactory::class)->willReturn(false);

        $bus = $busFactory($container->reveal());

        $bus->dispatch($message->reveal());

        $this->assertTrue($handlerWasCalled);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_bus_and_attaches_the_message_factory_defined_via_configuration(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $message = $this->prophesize(Message::class);
        $messageFactory = $this->prophesize(MessageFactory::class);

        $message->messageName()->willReturn('test_message');
        $handlerWasCalled = false;

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'router' => [
                            'type' => RegexRouter::class,
                            'routes' => [
                                '/^test_./' => function (Message $message) use (&$handlerWasCalled): void {
                                    $handlerWasCalled = true;
                                },
                            ],
                        ],
                        'message_factory' => 'custom_message_factory',
                    ],
                ],
            ],
        ]);

        $container->has('custom_message_factory')->willReturn(true);
        $container->get('custom_message_factory')->willReturn($messageFactory);

        $bus = $busFactory($container->reveal());

        $bus->dispatch($message->reveal());

        $this->assertTrue($handlerWasCalled);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_decorates_router_with_async_switch_and_pulls_async_message_producer_from_container(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $message = $this->prophesize(AsyncMessage::class);
        $messageFactory = $this->prophesize(MessageFactory::class);
        $messageProducer = new NoopMessageProducer();
        $container->get('noop_message_producer')->willReturn($messageProducer);

        $message->messageName()->willReturn('test_message');
        $message->metadata()->willReturn([]);
        $message->withAddedMetadata('handled-async', true)->willReturn($message->reveal());
        $handlerWasCalled = false;

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'router' => [
                            'async_switch' => 'noop_message_producer',
                            'type' => RegexRouter::class,
                            'routes' => [
                                '/^test_./' => function (Message $message) use (&$handlerWasCalled): void {
                                    $handlerWasCalled = true;
                                },
                            ],
                        ],
                        'message_factory' => 'custom_message_factory',
                    ],
                ],
            ],
        ]);

        $container->has('custom_message_factory')->willReturn(true);
        $container->get('custom_message_factory')->willReturn($messageFactory);

        $bus = $busFactory($container->reveal());

        $bus->dispatch($message->reveal());

        $this->assertFalse($handlerWasCalled);
        $this->assertTrue($messageProducer->isInvoked());
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_enables_handler_location_by_default(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $message = $this->prophesize(Message::class);

        $message->messageName()->willReturn('test_message')->shouldBeCalled();
        $handlerWasCalled = false;

        $container->has('config')->willReturn(true)->shouldBeCalled();
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'router' => [
                            'routes' => [
                                'test_message' => 'handler_service_id',
                            ],
                        ],
                    ],
                ],
            ],
        ])->shouldBeCalled();

        $container->has('handler_service_id')->willReturn(true)->shouldBeCalled();
        $container->get('handler_service_id')->willReturn(function (Message $message) use (&$handlerWasCalled): void {
            $handlerWasCalled = true;
        })->shouldBeCalled();

        $container->has(MessageFactory::class)->willReturn(false)->shouldBeCalled();

        $bus = $busFactory($container->reveal());

        $bus->dispatch($message->reveal());

        $this->assertTrue($handlerWasCalled);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_provides_possibility_to_disable_handler_location(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $message = $this->prophesize(Message::class);

        $message->messageName()->willReturn('test_message');

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'router' => [
                            'routes' => [
                                'test_message' => 'handler_service_id',
                            ],
                        ],
                        'enable_handler_location' => false,
                    ],
                ],
            ],
        ]);

        $container->has(MessageFactory::class)->willReturn(false);

        $container->has('handler_service_id')->shouldNotBeCalled();

        $bus = $busFactory($container->reveal());

        $bus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $e): void {
                $e->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED, true);
            },
            MessageBus::PRIORITY_INVOKE_HANDLER
        );

        $bus->dispatch($message->reveal());
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_can_handle_application_config_being_of_type_array_access(
        string $busClass,
        string $busConfigKey,
        AbstractBusFactory $busFactory
    ): void {
        $container = $this->prophesize(ContainerInterface::class);
        $firstPlugin = $this->prophesize(Plugin::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(new \ArrayObject([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'plugins' => [
                            'first_plugin_service_id',
                        ],
                    ],
                ],
            ],
        ]));

        $firstPlugin->attachToMessageBus(Argument::type(MessageBus::class))->shouldBeCalled();

        $container->has('first_plugin_service_id')->willReturn(true);
        $container->get('first_plugin_service_id')->willReturn($firstPlugin->reveal());

        $container->has(MessageFactory::class)->willReturn(false);

        $bus = $busFactory($container->reveal());

        $this->assertInstanceOf($busClass, $bus);
    }

    /**
     * @test
     * @dataProvider provideBusFactoryClasses
     */
    public function it_creates_a_bus_from_static_call(string $busClass, string $busFactoryClass): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->has(MessageFactory::class)->willReturn(false);
        $container->get('config')->willReturn([]);

        $factory = [$busFactoryClass, 'other_config_id'];
        $this->assertInstanceOf($busClass, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_without_container_on_static_call(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The first argument must be of type Psr\Container\ContainerInterface');

        CommandBusFactory::other_config_id();
    }

    public function provideBusFactoryClasses(): array
    {
        return [
            [CommandBus::class, CommandBusFactory::class],
            [EventBus::class, EventBusFactory::class],
            [QueryBus::class, QueryBusFactory::class],
        ];
    }

    public function provideBuses(): array
    {
        return [
            [CommandBus::class, 'command_bus', new CommandBusFactory()],
            [EventBus::class, 'event_bus', new EventBusFactory()],
            [QueryBus::class, 'query_bus', new QueryBusFactory()],
        ];
    }
}
