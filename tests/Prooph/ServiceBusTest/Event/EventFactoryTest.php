<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:06
 */

namespace Prooph\ServiceBusTest\Event;

use Prooph\ServiceBus\Event\EventFactory;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\StandardMessage;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;
use ValueObjects\DateTime\DateTime;

/**
 * Class EventFactoryTest
 *
 * @package Prooph\ServiceBusTest\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_event_from_message()
    {
        $eventFactory = new EventFactory();

        $header = new MessageHeader(Uuid::uuid4(), DateTime::now(), 1, 'test-case', MessageHeader::TYPE_EVENT);

        $message = new StandardMessage(
            'Prooph\ServiceBusTest\Mock\SomethingDone',
            $header,
            array('data' => 'test data')
        );

        $event = $eventFactory->fromMessage($message);

        $eventHeader = new MessageHeader(
            $event->uuid(),
            $event->occurredOn(),
            $event->version(),
            'test-case',
            MessageHeader::TYPE_EVENT
        );

        $this->assertTrue($event instanceof SomethingDone);
        $this->assertTrue($eventHeader->sameHeaderAs($header));
        $this->assertEquals('test data', $event->data());
    }
}
 