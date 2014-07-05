<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 20:15
 */

namespace Prooph\ServiceBusTest\Command;

use Prooph\ServiceBus\Command\CommandBus;
use Prooph\ServiceBus\Command\CommandReceiver;
use Prooph\ServiceBus\Message\InMemoryMessageDispatcher;
use Prooph\ServiceBus\Message\MessageFactory;
use Prooph\ServiceBus\Message\Queue;
use Prooph\ServiceBus\Service\CommandReceiverLoader;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\HandleCommandHandler;
use Prooph\ServiceBusTest\TestCase;
use Zend\EventManager\EventInterface;

/**
 * Class CommandBusTest
 *
 * @package Prooph\ServiceBusTest\Command
 * @author Alexander Miertsch <contact@prooph.de>
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
                'Prooph\ServiceBusTest\Mock\DoSomething' => 'do_something_handler'
            ),
            $serviceBusManager
        );

        $commandReceiverLoader = new CommandReceiverLoader();

        $commandReceiverLoader->setService('test-case-bus', $commandReceiver);

        $messageDispatcher->registerCommandReceiverLoaderForQueue($queue, $commandReceiverLoader);

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

    /**
     * @test
     */
    public function it_triggers_pre_and_post_send_events()
    {
        $preIsTriggered = false;
        $postIsTriggered = false;

        $doSomething = DoSomething::fromData('test payload');

        $this->commandBus->events()->attach('send.pre', function(EventInterface $e) use (&$preIsTriggered, $doSomething) {
            $this->assertSame($doSomething, $e->getParam('command'));
            $preIsTriggered = true;
        });

        $messageFactory = new MessageFactory();

        $message = $messageFactory->fromCommand($doSomething, 'test-case-bus');

        $this->commandBus->events()->attach(
            'send.post',
            function (EventInterface $e) use (&$postIsTriggered, $doSomething, $message) {
                $this->assertSame($doSomething, $e->getParam('command'));
                $this->assertTrue($message->header()->sameHeaderAs($e->getParam('message')->header()));
                $postIsTriggered = true;
            }
        );

        $this->commandBus->send($doSomething);

        $this->assertTrue($preIsTriggered);
        $this->assertTrue($postIsTriggered);
    }
}
 