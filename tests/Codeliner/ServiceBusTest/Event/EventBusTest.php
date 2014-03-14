<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 23:02
 */

namespace Codeliner\ServiceBusTest\Event;

use Codeliner\ServiceBus\Event\EventBus;
use Codeliner\ServiceBus\Event\EventReceiver;
use Codeliner\ServiceBus\Message\InMemoryMessageDispatcher;
use Codeliner\ServiceBus\Message\Queue;
use Codeliner\ServiceBus\Service\EventReceiverManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\OnEventHandler;
use Codeliner\ServiceBusTest\Mock\SomethingDone;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class EventBusTest
 *
 * @package Codeliner\ServiceBusTest\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventBusTest extends TestCase
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var OnEventHandler
     */
    private $somethingDoneHandler;

    protected function setUp()
    {
        //The complete setup is done by hand to demonstrate the dependencies
        $queue = new Queue('local');

        $messageDispatcher = new InMemoryMessageDispatcher();

        $this->somethingDoneHandler = new OnEventHandler();

        $serviceBusManager = new ServiceBusManager();

        $serviceBusManager->setService('something_done_handler', $this->somethingDoneHandler);

        $eventReceiver = new EventReceiver(
            array(
                'Codeliner\ServiceBusTest\Mock\SomethingDone' => 'something_done_handler'
            ),
            $serviceBusManager
        );

        $eventReceiverManager = new EventReceiverManager();

        $eventReceiverManager->setService('test-case-bus', $eventReceiver);

        $messageDispatcher->registerEventReceiverManagerForQueue($queue, $eventReceiverManager);

        $this->eventBus = new EventBus('test-case-bus', $messageDispatcher, array($queue));
    }

    /**
     * @test
     */
    public function it_sends_a_command_to_the_defined_queue_by_using_provided_message_dispatcher()
    {
        $somethingDone = SomethingDone::fromData('test payload');

        $this->eventBus->publish($somethingDone);

        $this->assertEquals('test payload', $this->somethingDoneHandler->lastEvent()->data());
    }
}
 