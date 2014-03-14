<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:06
 */

namespace Codeliner\ServiceBusTest\Event;

use Codeliner\ServiceBus\Event\EventFactory;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBusTest\Mock\SomethingDone;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class EventFactoryTest
 *
 * @package Codeliner\ServiceBusTest\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_event_from_message()
    {
        $eventFactory = new EventFactory();

        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_EVENT);

        $message = new StandardMessage(
            'Codeliner\ServiceBusTest\Mock\SomethingDone',
            $header,
            array('data' => 'test data')
        );

        $event = $eventFactory->fromMessage($message);

        $eventHeader = new MessageHeader(
            $event->uuid(),
            $event->createdOn(),
            $event->version(),
            'test-case',
            MessageHeader::TYPE_EVENT
        );

        $this->assertTrue($event instanceof SomethingDone);
        $this->assertTrue($eventHeader->sameHeaderAs($header));
        $this->assertEquals('test data', $event->data());
    }
}
 