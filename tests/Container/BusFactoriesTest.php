<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/16/15 - 9:34 PM
 */

namespace Prooph\ServiceBusTest\Factory;

use Interop\Container\ContainerInterface;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Container\CommandBusFactory;
use Prooph\ServiceBus\Container\EventBusFactory;
use Prooph\ServiceBus\Container\QueryBusFactory;
use Prooph\ServiceBus\Plugin\Router\RegexRouter;
use Prooph\ServiceBus\QueryBus;
use Prooph\ServiceBusTest\TestCase;
use Prophecy\Argument;

/**
 * Class BusFactoriesTest
 *
 * @package Prooph\ServiceBusTest\Container
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class BusFactoriesTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_bus_without_needing_a_application_config($busClass, $busConfigKey, $busFactory)
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(false);
        $container->has(MessageFactory::class)->willReturn(false);

        $bus =  $busFactory($container->reveal());

        $this->assertInstanceOf($busClass, $bus);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_bus_without_needing_prooph_config($busClass, $busConfigKey, $busFactory)
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([]);
        $container->has(MessageFactory::class)->willReturn(false);

        $bus =  $busFactory($container->reveal());

        $this->assertInstanceOf($busClass, $bus);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_new_bus_with_all_plugins_attached_using_a_container_and_configuration($busClass, $busConfigKey, $busFactory)
    {
        $container = $this->prophesize(ContainerInterface::class);
        $firstPlugin = $this->prophesize(ActionEventListenerAggregate::class);
        $secondPlugin = $this->prophesize(ActionEventListenerAggregate::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'plugins' => [
                            'first_plugin_service_id',
                            'second_plugin_service_id'
                        ]
                    ]
                ]
            ]
        ]);


        $firstPlugin->attach(Argument::type(ActionEventEmitter::class))->shouldBeCalled();
        $secondPlugin->attach(Argument::type(ActionEventEmitter::class))->shouldBeCalled();

        $container->has('first_plugin_service_id')->willReturn(true);
        $container->get('first_plugin_service_id')->willReturn($firstPlugin->reveal());
        $container->has('second_plugin_service_id')->willReturn(true);
        $container->get('second_plugin_service_id')->willReturn($secondPlugin->reveal());

        $container->has(MessageFactory::class)->willReturn(false);

        $bus =  $busFactory($container->reveal());

        $this->assertInstanceOf($busClass, $bus);
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     * @dataProvider provideBuses
     */
    public function it_throws_a_runtime_exception_if_plugin_is_not_registered_in_container($busClass, $busConfigKey, $busFactory)
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'plugins' => [
                            'plugin_service_id',
                        ]
                    ]
                ]
            ]
        ]);

        $container->has('plugin_service_id')->willReturn(false);

        $container->has(MessageFactory::class)->willReturn(false);

        $busFactory($container->reveal());
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_creates_a_bus_with_the_default_router_attached_if_routes_are_configured($busClass, $busConfigKey, $busFactory)
    {
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
                                'test_message' => function (Message $message) use (&$handlerWasCalled) {
                                        $handlerWasCalled = true;
                                    }
                            ]
                        ]
                    ]
                ]
            ]
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
    public function it_creates_a_bus_and_attaches_the_router_defined_via_configuration($busClass, $busConfigKey, $busFactory)
    {
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
                                '/^test_./' => function (Message $message) use (&$handlerWasCalled) {
                                        $handlerWasCalled = true;
                                    }
                            ]
                        ]
                    ]
                ]
            ]
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
    public function it_enables_handler_location_by_default($busClass, $busConfigKey, $busFactory)
    {
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
                                'test_message' => 'handler_service_id'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $container->has('handler_service_id')->willReturn(true);
        $container->get('handler_service_id')->willReturn(function (Message $message) use (&$handlerWasCalled) {
            $handlerWasCalled = true;
        });

        $container->has(MessageFactory::class)->willReturn(false);

        $bus = $busFactory($container->reveal());

        $bus->dispatch($message->reveal());

        $this->assertTrue($handlerWasCalled);
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_provides_possibility_to_disable_handler_location($busClass, $busConfigKey, $busFactory)
    {
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
                                'test_message' => 'handler_service_id'
                            ]
                        ],
                        'enable_handler_location' => false,
                    ]
                ]
            ]
        ]);

        $container->has(MessageFactory::class)->willReturn(false);

        $container->has('handler_service_id')->shouldNotBeCalled();

        $bus = $busFactory($container->reveal());

        $bus->dispatch($message->reveal());
    }

    /**
     * @test
     * @dataProvider provideBuses
     */
    public function it_can_handle_application_config_being_of_type_array_access($busClass, $busConfigKey, $busFactory)
    {
        $container = $this->prophesize(ContainerInterface::class);
        $firstPlugin = $this->prophesize(ActionEventListenerAggregate::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(new \ArrayObject([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => [
                        'plugins' => [
                            'first_plugin_service_id',
                        ]
                    ]
                ]
            ]
        ]));


        $firstPlugin->attach(Argument::type(ActionEventEmitter::class))->shouldBeCalled();

        $container->has('first_plugin_service_id')->willReturn(true);
        $container->get('first_plugin_service_id')->willReturn($firstPlugin->reveal());

        $container->has(MessageFactory::class)->willReturn(false);

        $bus =  $busFactory($container->reveal());

        $this->assertInstanceOf($busClass, $bus);
    }

    /**
     * @test
     * @dataProvider provideBuses
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_an_exception_if_application_config_is_neither_array_nor_array_access($busClass, $busConfigKey, $busFactory)
    {
        $container = $this->prophesize(ContainerInterface::class);

        $configObject = new \stdClass();

        $configObject->prooph = [
            'service_bus' => [
                $busConfigKey => [
                    'plugins' => [
                        'first_plugin_service_id',
                    ]
                ]
            ]
        ];

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($configObject);

        $bus =  $busFactory($container->reveal());
    }

    /**
     * @test
     * @dataProvider provideBuses
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_an_exception_if_bus_config_is_neither_array_nor_array_access($busClass, $busConfigKey, $busFactory)
    {
        $container = $this->prophesize(ContainerInterface::class);

        $busConfig = new \stdClass();

        $busConfig->plugins = [];

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(new \ArrayObject([
            'prooph' => [
                'service_bus' => [
                    $busConfigKey => $busConfig
                ]
            ]
        ]));

        $bus =  $busFactory($container->reveal());
    }


    public function provideBuses()
    {
        return [
            [CommandBus::class, 'command_bus', new CommandBusFactory()],
            [EventBus::class, 'event_bus', new EventBusFactory()],
            [QueryBus::class, 'query_bus', new QueryBusFactory()],
        ];
    }
}
