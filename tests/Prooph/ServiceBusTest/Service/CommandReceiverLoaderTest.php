<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 22:04
 */

namespace Prooph\ServiceBusTest\Service;

use Prooph\ServiceBus\Service\CommandReceiverLoader;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\ServiceBusConfiguration;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class CommandReceiverLoaderTest
 *
 * @package Prooph\ServiceBusTest\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandReceiverLoaderTest extends TestCase
{
    /**
     * @var ServiceBusManager
     */
    private $serviceBusManager;

    /**
     * @var CommandReceiverLoader
     */
    private $commandReceiverLoader;

    protected function setUp()
    {
        $config = array(
            Definition::COMMAND_MAP => array(
                //DoSomething command is mapped to the DoSometingHandler alias
                'Prooph\ServiceBusTest\Mock\DoSomething' => 'do_something_handler'
            ),
            Definition::COMMAND_HANDLER_INVOKE_STRATEGIES => array(
                //Alias of the DoSomethingInvokeStrategy
                'do_something_invoke_strategy'
            )
        );

        $this->serviceBusManager = new ServiceBusManager(new ServiceBusConfiguration($config));

        $this->commandReceiverLoader = new CommandReceiverLoader();

        //Set MainServiceManager as ServiceLocator for the CommandReceiverLoader
        $this->commandReceiverLoader->setServiceLocator($this->serviceBusManager);
    }

    /**
     * @test
     */
    public function it_returns_the_default_command_receiver()
    {
        $commandReceiver = $this->commandReceiverLoader->get('test-case-bus');

        $this->assertInstanceOf('Prooph\ServiceBus\Command\CommandReceiver', $commandReceiver);
    }
}