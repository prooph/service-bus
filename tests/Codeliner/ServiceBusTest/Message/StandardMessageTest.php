<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 18:43
 */

namespace Codeliner\ServiceBusTest\Message;

use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class StandardMessageTest
 *
 * @package Codeliner\ServiceBusTest\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
 