<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 20:41
 */

namespace Codeliner\ServiceBusTest\Command;

use Codeliner\ServiceBus\Command\CommandFactory;
use Codeliner\ServiceBus\Command\DefaultCommandReceiverFactory;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBus\Service\CommandReceiverManager;
use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\InvokeStrategyManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\DoSomethingHandler;
use Codeliner\ServiceBusTest\Mock\DoSomethingInvokeStrategy;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class DefaultCommandReceiverFactoryTest
 *
 * @package Codeliner\ServiceBusTest\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
     * @var CommandReceiverManager
     */
    private $commandReceiverManager;

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

        $invokeStrategyManager = new InvokeStrategyManager();

        //Register DoSomethingInvokeStrategy as Service
        $invokeStrategyManager->setService('do_something_invoke_strategy', new DoSomethingInvokeStrategy());

        $this->serviceBusManager->setAllowOverride(true);

        //Register InvokeStrategyManager as Service
        $this->serviceBusManager->setService(Definition::INVOKE_STRATEGY_MANAGER, $invokeStrategyManager);

        //Register CommandFactory as Service, this is not necessary but we do it for testing purposes
        $this->serviceBusManager->setService(Definition::COMMAND_FACTORY, new CommandFactory());

        $this->commandReceiverManager = new CommandReceiverManager();

        //Set MainServiceManager as ServiceLocator for the CommandReceiverManager
        $this->commandReceiverManager->setServiceLocator($this->serviceBusManager);
    }

    /**
     * @test
     */
    public function it_can_create_a_command_receiver()
    {
        $defaultCommandReceiverFactory = new DefaultCommandReceiverFactory();

        $this->assertTrue(
            $defaultCommandReceiverFactory
                ->canCreateServiceWithName($this->commandReceiverManager, 'testcasebus', 'test-case-bus')
        );
    }

    /**
     * @test
     */
    public function it_creates_a_fully_configured_command_receiver()
    {
        $defaultCommandReceiverFactory = new DefaultCommandReceiverFactory();

        $commandReceiver = $defaultCommandReceiverFactory
            ->createServiceWithName($this->commandReceiverManager, 'testcasebus', 'test-case-bus');

        $this->assertSame(
            $this->serviceBusManager->get(Definition::COMMAND_FACTORY),
            $commandReceiver->getCommandFactory()
        );

        $this->assertSame(
            $this->serviceBusManager->get(Definition::INVOKE_STRATEGY_MANAGER),
            $commandReceiver->getInvokeStrategyManager()
        );

        $this->assertEquals(
            array('do_something_invoke_strategy'),
            $commandReceiver->getInvokeStrategies()
        );

        $message = new StandardMessage(
            'Codeliner\ServiceBusTest\Mock\DoSomething',
            new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case-bus'),
            array('data' => 'test payload')
        );

        $commandReceiver->handle($message);

        $this->assertEquals('test payload', $this->doSomethingHandler->lastCommand()->data());
    }
}
