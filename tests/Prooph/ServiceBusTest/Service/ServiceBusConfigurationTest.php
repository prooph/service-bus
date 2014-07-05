<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 19:01
 */

namespace Prooph\ServiceBusTest\Service;

use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\ServiceBusConfiguration;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class ServiceBusConfigurationTest
 *
 * @package Prooph\ServiceBusTest\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ServiceBusConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_up_invoke_strategy_manager_with_configured_invoke_strategy()
    {
        $serviceBusConfiguration = new ServiceBusConfiguration(array(
            Definition::CONFIG_ROOT => array(
                Definition::COMMAND_HANDLER_INVOKE_STRATEGIES => array(
                    'do_something_invoke_strategy'
                ),
                Definition::INVOKE_STRATEGY_LOADER => array(
                    'invokables' => array(
                        'do_something_invoke_strategy' => 'Prooph\ServiceBusTest\Mock\DoSomethingInvokeStrategy'
                    )
                )
            )
        ));

        $sbm = new ServiceBusManager($serviceBusConfiguration);

        $invokeStrategy = $sbm->get(Definition::INVOKE_STRATEGY_LOADER)->get('do_something_invoke_strategy');

        $this->assertInstanceOf('Prooph\ServiceBusTest\Mock\DoSomethingInvokeStrategy', $invokeStrategy);
    }

    /**
     * @test
     */
    public function it_sets_up_command_bus_loader_with_configured_factory()
    {
        $serviceBusConfiguration = new ServiceBusConfiguration(array(
            Definition::CONFIG_ROOT => array(
                Definition::COMMAND_BUS_LOADER => array(
                    'factories' => array(
                        'mock-custom-bus' => 'Prooph\ServiceBusTest\Mock\Configtest\CustomBusFactory',
                    )
                )
            )
        ));

        $sbm = new ServiceBusManager($serviceBusConfiguration);

        $commandBus = $sbm->get(Definition::COMMAND_BUS_LOADER)->get('mock-custom-bus');

        $this->assertEquals("Created via factory", $commandBus->message);
    }

    /**
     * @test
     */
    public function it_sets_up_command_receiver_loader_with_configured_factory()
    {
        $serviceBusConfiguration = new ServiceBusConfiguration(array(
            Definition::CONFIG_ROOT => array(
                Definition::COMMAND_RECEIVER_LOADER => array(
                    'factories' => array(
                        'mock-custom-receiver' => 'Prooph\ServiceBusTest\Mock\Configtest\CustomCommandReceiverFactory',
                    )
                )
            )
        ));

        $sbm = new ServiceBusManager($serviceBusConfiguration);

        $customReceiver = $sbm->get(Definition::COMMAND_RECEIVER_LOADER)->get('mock-custom-receiver');

        $this->assertEquals("Created via factory", $customReceiver->message);
    }
}
 