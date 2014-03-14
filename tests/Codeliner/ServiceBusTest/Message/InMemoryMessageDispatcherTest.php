<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.03.14 - 21:19
 */

namespace Codeliner\ServiceBusTest\Message;

use Codeliner\ServiceBus\Message\InMemoryMessageDispatcher;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\Queue;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\ServiceBusConfiguration;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\HandleCommandHandler;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class InMemoryMessageDispatcherTest
 *
 * @package Codeliner\ServiceBusTest\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class InMemoryMessageDispatcherTest extends TestCase
{
    /**
     * @var InMemoryMessageDispatcher
     */
    private $messageDispatcher;

    /**
     * @var HandleCommandHandler
     */
    private $commandHandler;


    protected function setUp()
    {
        $config = new ServiceBusConfiguration();

        $this->commandHandler = new HandleCommandHandler();

        $config->setCommandMap('test-case-bus', array(
            'Codeliner\ServiceBusTest\Mock\DoSomething' => 'do_something_handler'
        ));

        $config->addCommandHandler('do_something_handler', $this->commandHandler);

        $serviceBus = new ServiceBusManager($config);

        $this->messageDispatcher = new InMemoryMessageDispatcher();

        $localQueue = new Queue('local');

        $this->messageDispatcher->registerCommandReceiverManagerForQueue(
            $localQueue,
            $serviceBus->get(Definition::COMMAND_RECEIVER_MANAGER)
        );
    }

    /**
     * @test
     */
    public function it_dispatches_a_message_synchronous()
    {
        $message = new StandardMessage(
            'Codeliner\ServiceBusTest\Mock\DoSomething',
            new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case-bus', MessageHeader::TYPE_COMMAND),
            array('data' => 'test payload')
        );

        $localQueue = new Queue('local');

        $this->messageDispatcher->dispatch($localQueue, $message);

        $this->assertEquals('test payload', $this->commandHandler->lastCommand()->data());
    }
}
