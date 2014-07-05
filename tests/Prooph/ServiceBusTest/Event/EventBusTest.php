<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 23:02
 */

namespace Prooph\ServiceBusTest\Event;

use Prooph\ServiceBus\Event\EventBus;
use Prooph\ServiceBus\Event\EventReceiver;
use Prooph\ServiceBus\Message\InMemoryMessageDispatcher;
use Prooph\ServiceBus\Message\MessageFactory;
use Prooph\ServiceBus\Message\Queue;
use Prooph\ServiceBus\Service\EventBusLoader;
use Prooph\ServiceBus\Service\EventFactoryLoader;
use Prooph\ServiceBus\Service\EventReceiverLoader;
use Prooph\ServiceBus\Service\MessageFactoryLoader;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\OnEventHandler;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;
use Zend\EventManager\EventInterface;

/**
 * Class EventBusTest
 *
 * @package Prooph\ServiceBusTest\Event
 * @author Alexander Miertsch <contact@prooph.de>
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
                'Prooph\ServiceBusTest\Mock\SomethingDone' => 'something_done_handler'
            ),
            $serviceBusManager
        );

        $eventReceiver->setEventFactoryLoader(new EventFactoryLoader());

        $eventReceiverLoader = new EventReceiverLoader();

        $eventReceiverLoader->setService('test-case-bus', $eventReceiver);

        $messageDispatcher->registerEventReceiverLoaderForQueue($queue, $eventReceiverLoader);
        $messageDispatcher->registerEventReceiverLoaderForQueue($queue2, $eventReceiverLoader);

        $this->eventBus = new EventBus('test-case-bus', $messageDispatcher, array($queue, $queue2));

        $this->eventBus->setMessageFactoryLoader(new MessageFactoryLoader());
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
                $this->assertInstanceOf('Prooph\ServiceBus\Message\QueueInterface', $e->getParam('queue'));
                $prePublishOnQueueTriggered = true;
            }
        );

        $this->eventBus->events()->attach(
            'publish_on_queue.post',
            function (EventInterface $e) use (&$postPublishOnQueueTriggered, $message) {
                $this->assertTrue($message->header()->sameHeaderAs($e->getParam('message')->header()));
                $this->assertInstanceOf('Prooph\ServiceBus\Message\QueueInterface', $e->getParam('queue'));
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
 