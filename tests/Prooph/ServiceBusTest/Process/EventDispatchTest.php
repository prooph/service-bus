<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 23.09.14 - 20:03
 */

namespace Prooph\ServiceBusTest\Process;

use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Process\EventDispatch;
use Prooph\ServiceBusTest\TestCase;
use Zend\Stdlib\ArrayObject;

/**
 * Class EventDispatchTest
 *
 * @package Prooph\ServiceBusTest\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventDispatchTest extends TestCase
{
    //@TODO Add tests for initialize with MessageNameProvider and MessageInterface events

    /**
     * @test
     */
    public function it_is_initialized_with_an_event_and_an_event_bus_as_target()
    {
        $event = new \ArrayObject(array('name' => 'SomethingDone'));

        $eventBus = new EventBus();

        $eventDispatch = EventDispatch::initializeWith($event, $eventBus);

        $this->assertSame($event, $eventDispatch->getEvent());
        $this->assertSame($eventBus, $eventDispatch->getTarget());
        $this->assertEquals(EventDispatch::INITIALIZE, $eventDispatch->getName());
    }

    /**
     * @test
     */
    public function it_replaces_event_with_a_new_one()
    {
        $otherEvent = new \ArrayObject(array('name' => 'SomethingDifferentDone'));

        $eventDispatch = $this->getNewEventDispatch();

        $eventDispatch->setEvent($otherEvent);

        $this->assertSame($otherEvent, $eventDispatch->getEvent());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_event_name()
    {
        $eventDispatch = $this->getNewEventDispatch();

        $eventDispatch->setEventName("SomethingDone");

        $this->assertEquals("SomethingDone", $eventDispatch->getEventName());
    }

    /**
     * @test
     */
    public function it_returns_null_when_event_name_is_not_set()
    {
        $this->assertNull($this->getNewEventDispatch()->getEventName());
    }

    /**
     * @test
     */
    public function it_only_accepts_a_string_as_event_name()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->getNewEventDispatch()->setEventName(123);
    }

    /**
     * @test
     */
    public function it_sets_and_gets_event_listener_strings()
    {
        $eventDispatch = $this->getNewEventDispatch();

        $eventDispatch->setEventListeners(array('SomethingDoneListener1', 'SomethingDoneListener2'));

        $this->assertEquals('SomethingDoneListener1', $eventDispatch->getEventListeners()[0]);
        $this->assertEquals('SomethingDoneListener2', $eventDispatch->getEventListeners()[1]);
    }

    /**
     * @test
     */
    public function it_accepts_an_object_as_event_listener()
    {
        $eventListener = new \stdClass();

        $eventDispatch = $this->getNewEventDispatch();

        $eventDispatch->addEventListener($eventListener);

        $this->assertSame($eventListener, $eventDispatch->getEventListeners()[0]);
    }

    /**
     * @test
     */
    public function it_accepts_a_callable_as_event_listener()
    {
        $evenListenerCallback = function ($event) {};

        $eventDispatch = $this->getNewEventDispatch();

        $eventDispatch->addEventListener($evenListenerCallback);

        $this->assertSame($evenListenerCallback, $eventDispatch->getEventListeners()[0]);
    }

    /**
     * @test
     */
    public function it_does_not_accept_a_non_callable_array_as_event_listener()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $eventDispatch = $this->getNewEventDispatch();

        $eventDispatch->addEventListener(array("SomethingDoneListener"));
    }

    /**
     * @test
     */
    public function it_has_always_a_logger_available()
    {
        $this->assertInstanceOf('Zend\Log\LoggerInterface', $this->getNewEventDispatch()->getLogger());
    }

    /**
     * @return EventDispatch
     */
    protected function getNewEventDispatch()
    {
        return EventDispatch::initializeWith(new \ArrayObject(array('name' => 'SomethingDone')), new EventBus());
    }
}
 