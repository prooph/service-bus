<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 18:33
 */

namespace Prooph\ServiceBusTest\Message;

use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class MessageHeaderTest
 *
 * @package Prooph\ServiceBusTest\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class MessageHeaderTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_uuid()
    {
        $uuid = Uuid::uuid4();

        $header = new MessageHeader($uuid, new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $this->assertEquals($uuid->toString(), $header->uuid()->toString());
    }

    /**
     * @test
     */
    public function it_has_a_created_on_datetime()
    {
        $createdOn = new \DateTime();

        $header = new MessageHeader(Uuid::uuid4(), $createdOn, 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $this->assertEquals($createdOn->getTimestamp(), $header->createdOn()->getTimestamp());
    }

    /**
     * @test
     */
    public function it_has_a_version()
    {
        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $this->assertEquals(1, $header->version());
    }

    /**
     * @test
     */
    public function it_has_a_sender()
    {
        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $this->assertEquals('test-case', $header->sender());
    }

    /**
     * @test
     */
    public function it_has_a_type()
    {
        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $this->assertEquals('command', $header->type());
    }

    /**
     * @test
     */
    public function it_converts_itself_to_array_and_back()
    {
        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $headerArray = $header->toArray();

        $sameHeader = MessageHeader::fromArray($headerArray);

        $this->assertEquals($header->toArray(), $sameHeader->toArray());
    }
}
 