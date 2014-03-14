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
use Codeliner\ServiceBus\Message\MessageFactory;
use Codeliner\ServiceBus\Message\Queue;
use Codeliner\ServiceBus\Service\EventBusManager;
use Codeliner\ServiceBus\Service\EventReceiverManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\OnEventHandler;
use Codeliner\ServiceBusTest\Mock\SomethingDone;
use Codeliner\ServiceBusTest\TestCase;
use Zend\EventManager\EventInterface;

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
        $queue  = new Queue('local');
        $queue2 = new Queue('local-2');

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
        $messageDispatcher->registerEventReceiverManagerForQueue($queue2, $eventReceiverManager);

        $this->eventBus = new EventBus('test-case-bus', $messageDispatcher, array($queue, $queue2));
    }

    /**
     * @test
     */
    public function it_sends_a_command_to_the_defined_queue_by_using_provided_message_dispatcher()
    {
        $somethingDone = SomethingDone::fromData('test payload');

        $this->eventBus->publish($somethingDone);

        $this->assertEquals('test payload', $this->somethingDoneHandler->lastEvent()->data());
        $this->assertEquals(2, $this->somethingDoneHandler->eventCount());
    }

    /**
     * @test
     */
    public function it_triggers_all_events()
    {
        $prePublishTriggered         = false;
        $prePublishOnQueueTriggered  = false;
        $postPublishOnQueueTriggered = false;
        $postPublishTriggered        = false;

        $somethingDone = SomethingDone::fromData('test payload');

        $messageFactory = new MessageFactory();

        $message = $messageFactory->fromEvent($somethingDone, 'test-case-bus');

        $this->eventBus->events()->attach(
            'publish.pre',
            function (EventInterface $e) use (&$prePublishTriggered, $somethingDone) {
                $this->assertSame($somethingDone, $e->getParam('event'));
                $prePublishTriggered = true;
            }
        );

        $this->eventBus->events()->attach(
            'publish_on_queue.pre',
            function (EventInterface $e) use (&$prePublishOnQueueTriggered, $message) {
                $this->assertTrue($message->header()->sameHeaderAs($e->getParam('message')->header()));
                $this->assertInstanceOf('Codeliner\ServiceBus\Message\QueueInterface', $e->getParam('queue'));
                $prePublishOnQueueTriggered = true;
            }
        );

        $this->eventBus->events()->attach(
            'publish_on_queue.post',
            function (EventInterface $e) use (&$postPublishOnQueueTriggered, $message) {
                $this->assertTrue($message->header()->sameHeaderAs($e->getParam('message')->header()));
                $this->assertInstanceOf('Codeliner\ServiceBus\Message\QueueInterface', $e->getParam('queue'));
                $postPublishOnQueueTriggered = true;
            }
        );

        $this->eventBus->events()->attach(
            'publish.post',
            function (EventInterface $e) use (&$postPublishTriggered, $somethingDone, $message) {
                $this->assertSame($somethingDone, $e->getParam('event'));
                $this->assertTrue($message->header()->sameHeaderAs($e->getParam('message')->header()));
                $postPublishTriggered = true;
            }
        );

        $this->eventBus->publish($somethingDone);

        $this->assertTrue($prePublishTriggered);
        $this->assertTrue($prePublishOnQueueTriggered);
        $this->assertTrue($postPublishOnQueueTriggered);
        $this->assertTrue($postPublishTriggered);
    }

    /**
     * @test
     */
    public function it_skips_queue_if_listener_stops_propagation()
    {
        $this->eventBus->events()->attach(
            'publish_on_queue.pre',
            function (EventInterface $e) {
                if ($e->getParam('queue')->name() === 'local') {
                    $e->stopPropagation(true);
                }
            }
        );

        $somethingDone = SomethingDone::fromData('test payload');

        $this->eventBus->publish($somethingDone);

        $this->assertEquals(1, $this->somethingDoneHandler->eventCount());
    }
}
 