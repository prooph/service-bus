<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 20:15
 */

namespace Codeliner\ServiceBusTest\Command;

use Codeliner\ServiceBus\Command\CommandBus;
use Codeliner\ServiceBus\Command\CommandReceiver;
use Codeliner\ServiceBus\Message\InMemoryMessageDispatcher;
use Codeliner\ServiceBus\Message\Queue;
use Codeliner\ServiceBus\Service\CommandReceiverManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\Mock\HandleCommandHandler;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class CommandBusTest
 *
 * @package Codeliner\ServiceBusTest\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandBusTest extends TestCase
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var HandleCommandHandler
     */
    private $doSomethingHandler;

    protected function setUp()
    {
        //The complete setup is done by hand to demonstrate the dependencies
        $queue = new Queue('local');

        $messageDispatcher = new InMemoryMessageDispatcher();

        $this->doSomethingHandler = new HandleCommandHandler();

        $serviceBusManager = new ServiceBusManager();

        $serviceBusManager->setService('do_something_handler', $this->doSomethingHandler);

        $commandReceiver = new CommandReceiver(
            array(
                'Codeliner\ServiceBusTest\Mock\DoSomething' => 'do_something_handler'
            ),
            $serviceBusManager
        );

        $commandReceiverManager = new CommandReceiverManager();

        $commandReceiverManager->setService('test-case-bus', $commandReceiver);

        $messageDispatcher->registerCommandReceiverManagerForQueue($queue, $commandReceiverManager);

        $this->commandBus = new CommandBus('test-case-bus', $messageDispatcher, $queue);
    }

    /**
     * @test
     */
    public function it_sends_a_command_to_the_defined_queue_by_using_provided_message_dispatcher()
    {
        $doSomething = DoSomething::fromData('test payload');

        $this->commandBus->send($doSomething);

        $this->assertEquals('test payload', $this->doSomethingHandler->lastCommand()->data());
    }
}
 