<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 20:41
 */

namespace Prooph\ServiceBusTest\Command;

use Prooph\ServiceBus\Command\CommandFactory;
use Prooph\ServiceBus\Command\DefaultCommandReceiverFactory;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\StandardMessage;
use Prooph\ServiceBus\Service\CommandReceiverLoader;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\InvokeStrategyLoader;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\DoSomethingHandler;
use Prooph\ServiceBusTest\Mock\DoSomethingInvokeStrategy;
use Prooph\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class DefaultCommandReceiverFactoryTest
 *
 * @package Prooph\ServiceBusTest\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultCommandReceiverFactoryTest extends TestCase
{
    /**
     * @var ServiceBusManager
     */
    private $serviceBusManager;

    /**
     * @var DoSomethingHandler
     */
    private $doSomethingHandler;

    /**
     * @var CommandReceiverLoader
     */
    private $commandReceiverLoader;

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
                        )
                    )
                ),
                Definition::COMMAND_HANDLER_INVOKE_STRATEGIES => array(
                    //Alias of the DoSomethingInvokeStrategy
                    'do_something_invoke_strategy'
                )
            )
        );

        //Add global config as service
        $this->serviceBusManager->setService('configuration', $config);

        //Should handle the DoSomething command
        $this->doSomethingHandler = new DoSomethingHandler();

        //Register DoSomethingHandler as Service
        $this->serviceBusManager->setService('do_something_handler', $this->doSomethingHandler);

        $invokeStrategyLoader = new InvokeStrategyLoader();

        //Register DoSomethingInvokeStrategy as Service
        $invokeStrategyLoader->setService('do_something_invoke_strategy', new DoSomethingInvokeStrategy());

        $this->serviceBusManager->setAllowOverride(true);

        //Register InvokeStrategyLoader as Service
        $this->serviceBusManager->setService(Definition::INVOKE_STRATEGY_LOADER, $invokeStrategyLoader);

        $this->commandReceiverLoader = new CommandReceiverLoader();

        //Set MainServiceManager as ServiceLocator for the CommandReceiverLoader
        $this->commandReceiverLoader->setServiceLocator($this->serviceBusManager);
    }

    /**
     * @test
     */
    public function it_can_create_a_command_receiver()
    {
        $defaultCommandReceiverFactory = new DefaultCommandReceiverFactory();

        $this->assertTrue(
            $defaultCommandReceiverFactory
                ->canCreateServiceWithName($this->commandReceiverLoader, 'testcasebus', 'test-case-bus')
        );
    }

    /**
     * @test
     */
    public function it_creates_a_fully_configured_command_receiver()
    {
        $defaultCommandReceiverFactory = new DefaultCommandReceiverFactory();

        $commandReceiver = $defaultCommandReceiverFactory
            ->createServiceWithName($this->commandReceiverLoader, 'testcasebus', 'test-case-bus');

        $this->assertSame(
            $this->serviceBusManager->get(Definition::INVOKE_STRATEGY_LOADER),
            $commandReceiver->getInvokeStrategyLoader()
        );

        $this->assertEquals(
            array('do_something_invoke_strategy'),
            $commandReceiver->getInvokeStrategies()
        );

        $message = new StandardMessage(
            'Prooph\ServiceBusTest\Mock\DoSomething',
            new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case-bus', MessageHeader::TYPE_COMMAND),
            array('data' => 'test payload')
        );

        $commandReceiver->handle($message);

        $this->assertEquals('test payload', $this->doSomethingHandler->lastCommand()->data());
    }
}
