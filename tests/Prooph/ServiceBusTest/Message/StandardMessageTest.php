<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 18:43
 */

namespace Prooph\ServiceBusTest\Message;

use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\StandardMessage;
use Prooph\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class StandardMessageTest
 *
 * @package Prooph\ServiceBusTest\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class StandardMessageTest extends TestCase
{
    /**
     * @var StandardMessage
     */
    private $message;

    /**
     * @var MessageHeader
     */
    private $header;

    protected function setUp()
    {
        $this->header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $this->message = new StandardMessage('TestMessage', $this->header, array('data' => 'a test'));
    }

    /**
     * @test
     */
    public function it_has_a_name()
    {
        $this->assertEquals('TestMessage', $this->message->name());
    }

    /**
     * @test
     */
    public function it_has_a_header()
    {
        $this->assertTrue($this->message->header()->sameHeaderAs($this->header));
    }

    /**
     * @test
     */
    public function it_has_a_payload()
    {
        $this->assertEquals(array('data' => 'a test'), $this->message->payload());
    }

    /**
     * @test
     */
    public function it_converts_itself_to_array_and_back()
    {
        $messageArray = $this->message->toArray();

        $sameMessage = StandardMessage::fromArray($messageArray);

        $this->assertEquals($this->message->name(), $sameMessage->name());
        $this->assertTrue($this->message->header()->sameHeaderAs($sameMessage->header()));
        $this->assertEquals($this->message->payload(), $sameMessage->payload());
    }
}
 