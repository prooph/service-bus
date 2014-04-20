<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:09
 */

namespace Prooph\ServiceBusTest\Event;

use Prooph\ServiceBus\Event\EventFactory;
use Prooph\ServiceBus\Event\EventReceiver;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\StandardMessage;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;
use Zend\EventManager\EventInterface;

/**
 * Class EventReceiverTest
 *
 * @package Prooph\ServiceBusTest\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventReceiverTest extends TestCase
{
    /**
     * @var EventReceiver
     */
    private $eventReceiver;

    public $eventHeader;

    public $eventPayload = null;

    public $called = 0;

    protected function setUp()
    {
        $eventHandlerLocator = new ServiceBusManager();

        $this->eventHeader  = null;
        $this->eventPayload = null;
        $this->called       = 0;

        $self = $this;

        $eventHandlerLocator->setService('test-case-callback', function (SomethingDone $anEvent) use ($self) {

            $eventHeader = new MessageHeader(
                $anEvent->uuid(),
                $anEvent->createdOn(),
                $anEvent->version(),
                'test-case',
                MessageHeader::TYPE_EVENT
            );

            $self->eventHeader  = $eventHeader;
            $self->eventPayload = $anEvent->payload();
            $self->called++;
        });

        //callback should be called twice that simulates multiple EventHandler
        $this->eventReceiver = new EventReceiver(
            array(
                'Prooph\ServiceBusTest\Mock\SomethingDone' => array('test-case-callback', 'test-case-callback')
            ),
            $eventHandlerLocator
        );
    }

    /**
     * @test
     */
    public function it_handles_a_message_and_calls_the_related_event_on_configured_handler()
    {
        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_EVENT);

        $message = new StandardMessage(
            'Prooph\ServiceBusTest\Mock\SomethingDone',
            $header,
            array('data' => 'test')
        );

        $this->eventReceiver->handle($message);

        $this->assertTrue($header->sameHeaderAs($this->eventHeader));
        $this->assertEquals(array('data' => 'test'), $this->eventPayload);
        $this->assertEquals(2, $this->called);
    }

    /**
     * @test
     */
    public function it_triggers_all_events()
    {
        $preHandleTriggered         = false;
        $preInvokeHandlerTriggered  = false;
        $postInvokeHandlerTriggered = false;
        $postHandleTriggered        = false;

        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_EVENT);

        $message = new StandardMessage(
            'Prooph\ServiceBusTest\Mock\SomethingDone',
            $header,
            array('data' => 'test')
        );

        $eventFactory = new EventFactory();

        $event = $eventFactory->fromMessage($message);

        $this->eventReceiver->events()->attach(
            'handle.pre',
            function (EventInterface $e) use (&$preHandleTriggered, $message) {
                $this->assertSame($message, $e->getParam('message'));
                $preHandleTriggered = true;
            }
        );

        $this->eventReceiver->events()->attach(
            'invoke_handler.pre',
            function (EventInterface $e) use (&$preInvokeHandlerTriggered, $event) {
                $this->assertSame($event->uuid(), $e->getParam('event')->uuid());
                $this->assertTrue(is_callable($e->getParam('handler')));
                $preInvokeHandlerTriggered = true;
            }
        );

        $this->eventReceiver->events()->attach(
            'invoke_handler.post',
            function (EventInterface $e) use (&$postInvokeHandlerTriggered, $event) {
                $this->assertSame($event->uuid(), $e->getParam('event')->uuid());
                $this->assertTrue(is_callable($e->getParam('handler')));
                $postInvokeHandlerTriggered = true;
            }
        );

        $this->eventReceiver->events()->attach(
            'handle.post',
            function (EventInterface $e) use (&$postHandleTriggered, $event, $message) {
                $this->assertSame($event->uuid(), $e->getParam('event')->uuid());
                $this->assertSame($message, $e->getParam('message'));
                $postHandleTriggered = true;
            }
        );

        $this->eventReceiver->handle($message);

        $this->assertTrue($preHandleTriggered);
        $this->assertTrue($preInvokeHandlerTriggered);
        $this->assertTrue($postInvokeHandlerTriggered);
        $this->assertTrue($postHandleTriggered);
    }

    /**
     * @test
     */
    public function it_skips_invoking_handler_if_listeners_stops_propagation()
    {
        $this->called = 0;

        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_EVENT);

        $message = new StandardMessage(
            'Prooph\ServiceBusTest\Mock\SomethingDone',
            $header,
            array('data' => 'test')
        );

        $this->eventReceiver->events()->attach(
            'invoke_handler.pre',
            function (EventInterface $e) {
                if ($this->called == 1) {
                    $e->stopPropagation(true);
                }
            }
        );

        $this->eventReceiver->handle($message);

        $this->assertEquals(1, $this->called);
    }
}
 