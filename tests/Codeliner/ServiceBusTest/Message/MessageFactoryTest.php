<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:47
 */

namespace Codeliner\ServiceBusTest\Message;

use Codeliner\ServiceBus\Message\MessageFactory;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\Mock\SomethingDone;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class MessageFactoryTest
 *
 * @package Codeliner\ServiceBusTest\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class MessageFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_message_from_command_and_sender_name()
    {
        $messageFactory = new MessageFactory();

        $doSomething = DoSomething::fromData('test command');

        $message = $messageFactory->fromCommand($doSomething, 'test-case-bus');

        $testHeader = new MessageHeader(
            $doSomething->uuid(),
            $doSomething->createdOn(),
            $doSomething->version(),
            'test-case-bus'
        );

        $this->assertTrue($message->header()->sameHeaderAs($testHeader));

        $this->assertEquals(array('data' => 'test command'), $message->payload());
    }

    /**
     * @test
     */
    public function it_creates_message_from_event_and_sender_name()
    {
        $messageFactory = new MessageFactory();

        $somethingDone = SomethingDone::fromData('test event');

        $message = $messageFactory->fromEvent($somethingDone, 'test-case-bus');

        $testHeader = new MessageHeader(
            $somethingDone->uuid(),
            $somethingDone->createdOn(),
            $somethingDone->version(),
            'test-case-bus'
        );

        $this->assertTrue($message->header()->sameHeaderAs($testHeader));

        $this->assertEquals(array('data' => 'test event'), $message->payload());
    }
}
 