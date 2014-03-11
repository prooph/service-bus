<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:56
 */

namespace Codeliner\ServiceBusTest\Command;

use Codeliner\ServiceBus\Command\DefaultCommandBusFactory;
use Codeliner\ServiceBus\Message\Queue;
use Codeliner\ServiceBus\Service\CommandBusManager;
use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\Mock\HandleCommandHandler;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class DefaultCommandBusFactoryTest
 *
 * @package Codeliner\ServiceBusTest\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
                            'Codeliner\ServiceBusTest\Mock\DoSomething' => 'do_something_handler'
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

        $inMemoryMessageDispatcher = $this->serviceBusManager->get(Definition::MESSAGE_DISPATCHER_MANAGER)
            ->get(Definition::IN_MEMORY_MESSAGE_DISPATCHER);

        $inMemoryMessageDispatcher->registerCommandReceiverManagerForQueue(
            new Queue('local'),
            $this->serviceBusManager->get(Definition::COMMAND_RECEIVER_MANAGER)
        );
    }

    /**
     * @test
     */
    public function it_creates_a_fully_configured_command_bus()
    {
        $commandBusManager = new CommandBusManager();
        $commandBusManager->setServiceLocator($this->serviceBusManager);

        $factory = new DefaultCommandBusFactory();

        $commandBus = $factory->createServiceWithName($commandBusManager, 'testcasebus', 'test-case-bus');

        $doSomething = DoSomething::fromData('test payload');

        $commandBus->send($doSomething);

        $this->assertEquals('test payload', $this->doSomethingHandler->lastCommand()->data());
    }
}
