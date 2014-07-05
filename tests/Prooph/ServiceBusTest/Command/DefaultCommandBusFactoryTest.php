<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:56
 */

namespace Prooph\ServiceBusTest\Command;

use Prooph\ServiceBus\Command\DefaultCommandBusFactory;
use Prooph\ServiceBus\Message\Queue;
use Prooph\ServiceBus\Service\CommandBusLoader;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\HandleCommandHandler;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class DefaultCommandBusFactoryTest
 *
 * @package Prooph\ServiceBusTest\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultCommandBusFactoryTest extends TestCase
{
    /**
     * @var ServiceBusManager
     */
    private $serviceBusManager;

    /**
     * @var HandleCommandHandler
     */
    private $doSomethingHandler;

    protected function setUp()
    {
        $this->serviceBusManager = new ServiceBusManager();

        $config = array(
            Definition::CONFIG_ROOT => array(
                Definition::COMMAND_BUS => array(
                    //name of the bus, must match with the Message.header.sender
                    'test-case-bus' => array(
                        Definition::COMMAND_MAP => array(
                            //DoSomething command is mapped to the DoSometingHandler alias
                            'Prooph\ServiceBusTest\Mock\DoSomething' => 'do_something_handler'
                        ),
                        Definition::QUEUE => 'local',
                        Definition::MESSAGE_DISPATCHER => Definition::IN_MEMORY_MESSAGE_DISPATCHER,
                    )
                ),
            )
        );

        //Add global config as service
        $this->serviceBusManager->setService('configuration', $config);

        //Should handle the DoSomething command
        $this->doSomethingHandler = new HandleCommandHandler();

        //Register DoSomethingHandler as Service
        $this->serviceBusManager->setService('do_something_handler', $this->doSomethingHandler);

        $inMemoryMessageDispatcher = $this->serviceBusManager->get(Definition::MESSAGE_DISPATCHER_LOADER)
            ->get(Definition::IN_MEMORY_MESSAGE_DISPATCHER);

        $inMemoryMessageDispatcher->registerCommandReceiverLoaderForQueue(
            new Queue('local'),
            $this->serviceBusManager->get(Definition::COMMAND_RECEIVER_LOADER)
        );
    }

    /**
     * @test
     */
    public function it_creates_a_fully_configured_command_bus()
    {
        $commandBusLoader = new CommandBusLoader();
        $commandBusLoader->setServiceLocator($this->serviceBusManager);

        $factory = new DefaultCommandBusFactory();

        $commandBus = $factory->createServiceWithName($commandBusLoader, 'testcasebus', 'test-case-bus');

        $doSomething = DoSomething::fromData('test payload');

        $commandBus->send($doSomething);

        $this->assertEquals('test payload', $this->doSomethingHandler->lastCommand()->data());
    }
}
