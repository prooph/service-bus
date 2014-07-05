<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.03.14 - 23:31
 */

namespace Prooph\ServiceBusTest\Initializer;

use Prooph\ServiceBus\Initializer\LocalSynchronousInitializer;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\ServiceBusConfiguration;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\HandleCommandHandler;
use Prooph\ServiceBusTest\Mock\OnEventHandler;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;
use Zend\ServiceManager\Config;

/**
 * Class LocalInitializerTest
 *
 * @package Prooph\ServiceBusTest\Initializer
 * @author Alexander Miertsch <contact@prooph.de>
 */
class LocalInitializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_initializes_a_local_service_bus_environment()
    {
        $serviceBusManager = new ServiceBusManager();

        $localEnv = new LocalSynchronousInitializer();

        $doSomething = DoSomething::fromData('test payload');
        $somethingDone = SomethingDone::fromData('event payload');

        $doSomethingHandler = new HandleCommandHandler();
        $somethingDoneHandler = new OnEventHandler();

        $localEnv->setCommandHandler($doSomething, $doSomethingHandler);
        $localEnv->addEventHandler($somethingDone, $somethingDoneHandler);
        $localEnv->addEventHandler($somethingDone, $somethingDoneHandler);

        $serviceBusManager->events()->attachAggregate($localEnv);

        $serviceBusManager->initialize();

        $serviceBusManager->getCommandBus()->send($doSomething);
        $serviceBusManager->getEventBus()->publish($somethingDone);

        $this->assertEquals('test payload', $doSomethingHandler->lastCommand()->data());
        $this->assertEquals('event payload', $somethingDoneHandler->lastEvent()->data());
        $this->assertEquals(2, $somethingDoneHandler->eventCount());
    }

    /**
     * @test
     */
    public function it_merges_configured_command_map_with_attached_command_handlers()
    {
        $serviceBusConfig = new ServiceBusConfiguration(array(
            Definition::CONFIG_ROOT => array(
                Definition::COMMAND_BUS => array(
                    'local-command-bus' => array(
                        Definition::COMMAND_MAP => array(
                            'My\Custom\Command' => 'My\Custom\CommandHandler'
                        )
                    )
                )
            )
        ));

        $serviceBusManager = new ServiceBusManager($serviceBusConfig);

        $localEnv = new LocalSynchronousInitializer();

        $doSomething = DoSomething::fromData('test payload');

        $doSomethingHandler = new HandleCommandHandler();

        $localEnv->setCommandHandler($doSomething, $doSomethingHandler);

        $serviceBusManager->events()->attachAggregate($localEnv);

        $serviceBusManager->initialize();

        $configuration = $serviceBusManager->get('configuration');

        $localCommandBusConfig = $configuration[Definition::CONFIG_ROOT][Definition::COMMAND_BUS]['local-command-bus'];

        $checkConfig = array(
            Definition::COMMAND_MAP => array(
                'My\Custom\Command' => 'My\Custom\CommandHandler',
                'Prooph\ServiceBusTest\Mock\DoSomething' => 'Prooph\ServiceBusTest\Mock\DoSomething_local_handler'
            ),
            Definition::QUEUE => 'local-queue',
            Definition::MESSAGE_DISPATCHER => 'in_memory_message_dispatcher'
        );

        $this->assertEquals($checkConfig, $localCommandBusConfig);
    }

    /**
     * @test
     */
    public function it_merges_configured_event_map_with_attached_command_handlers()
    {
        $somethingDone = SomethingDone::fromData('event payload');

        $serviceBusConfig = new ServiceBusConfiguration(array(
            Definition::CONFIG_ROOT => array(
                Definition::EVENT_BUS => array(
                    'local-event-bus' => array(
                        Definition::EVENT_MAP => array(
                            'My\Custom\Event' => array('My\Custom\EventHandler'),
                            get_class($somethingDone) => array('My\Custom\SomethingDoneHandler'),

                        )
                    )
                )
            )
        ));

        $serviceBusManager = new ServiceBusManager($serviceBusConfig);

        $localEnv = new LocalSynchronousInitializer();

        $somethingDoneHandler = new OnEventHandler();

        $localEnv->addEventHandler($somethingDone, $somethingDoneHandler);

        $serviceBusManager->events()->attachAggregate($localEnv);

        $serviceBusManager->initialize();

        $configuration = $serviceBusManager->get('configuration');

        $localEventBusConfig = $configuration[Definition::CONFIG_ROOT][Definition::EVENT_BUS]['local-event-bus'];

        $checkConfig = array(
            Definition::EVENT_MAP => array(
                'My\Custom\Event' => array('My\Custom\EventHandler'),
                get_class($somethingDone) => array(
                    'My\Custom\SomethingDoneHandler',
                    'Prooph\ServiceBusTest\Mock\SomethingDone_local_handler_0'
                ),

            ),
            Definition::QUEUE => 'local-queue',
            Definition::MESSAGE_DISPATCHER => 'in_memory_message_dispatcher'
        );

        $this->assertEquals($checkConfig, $localEventBusConfig);
    }
}
 