<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:16
 */

namespace Prooph\ServiceBusTest\Event;

use Prooph\ServiceBusTest\Mock\PayloadMockObject;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class EventTest
 *
 * @package Prooph\ServiceBusTest\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_payload_array()
    {
        $event = new SomethingDone('somenthing_done', array('data' => 'test payload'));

        $this->assertEquals(array('data' => 'test payload'), $event->payload());

        $payloadObject = new PayloadMockObject(array('data' => 'test payload'));

        $event = new SomethingDone('somenthing_done', $payloadObject);

        $this->assertEquals(array('data' => 'test payload'), $event->payload());
    }

    /**
     * @test
     */
    public function it_has_a_version()
    {
        $event = new SomethingDone('somenthing_done', array('data' => 'test payload'));

        $this->assertEquals(1, $event->version());

        $event = new SomethingDone('somenthing_done', array('data' => 'test payload'), 2);

        $this->assertEquals(2, $event->version());
    }

    /**
     * @test
     */
    public function it_has_a_uuid()
    {
        $event = new SomethingDone('somenthing_done', array('data' => 'test payload'));

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $event->uuid());

        $uuid = Uuid::uuid4();

        $event = new SomethingDone('somenthing_done', array('data' => 'test payload'), 2, $uuid);

        $this->assertEquals($uuid->toString(), $event->uuid()->toString());
    }

    /**
     * @test
     */
    public function it_has_a_created_on_datetime()
    {
        $event = new SomethingDone('somenthing_done', array('data' => 'test payload'));

        $this->assertInstanceOf('DateTime', $event->occurredOn());

        $occurredOn = new \DateTime('2014-03-14 21:27:00');

        $event = new SomethingDone('somenthing_done', array('data' => 'test payload'), 1, null, $occurredOn);

        $this->assertEquals($occurredOn->getTimestamp(), $event->occurredOn()->getTimestamp());
    }
}
 