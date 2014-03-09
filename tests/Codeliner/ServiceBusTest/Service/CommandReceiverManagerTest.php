<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 22:04
 */

namespace Codeliner\ServiceBusTest\Service;

use Codeliner\ServiceBus\Service\CommandReceiverManager;
use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class CommandReceiverManagerTest
 *
 * @package Codeliner\ServiceBusTest\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandReceiverManagerTest extends TestCase
{
    /**
     * @var ServiceBusManager
     */
    private $serviceBusManager;

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

        $this->commandReceiverManager = new CommandReceiverManager();

        //Set MainServiceManager as ServiceLocator for the CommandReceiverManager
        $this->commandReceiverManager->setServiceLocator($this->serviceBusManager);
    }

    /**
     * @test
     */
    public function it_returns_the_default_command_receiver()
    {
        $commandReceiver = $this->commandReceiverManager->get('test-case-bus');

        $this->assertInstanceOf('Codeliner\ServiceBus\Command\CommandReceiver', $commandReceiver);
    }
}