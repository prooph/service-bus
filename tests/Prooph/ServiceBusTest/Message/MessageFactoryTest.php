<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:47
 */

namespace Prooph\ServiceBusTest\Message;

use Prooph\ServiceBus\Message\MessageFactory;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class MessageFactoryTest
 *
 * @package Prooph\ServiceBusTest\Message
 * @author Alexander Miertsch <contact@prooph.de>
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

        $this->assertEquals(array('data' => 'test event'), $message->payload());
    }
}
 