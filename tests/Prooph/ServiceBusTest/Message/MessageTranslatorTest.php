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

use Prooph\ServiceBus\Message\MessageTranslator;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class MessageTranslatorTest
 *
 * @package Prooph\ServiceBusTest\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class MessageTranslatorTest extends TestCase
{
    /**
     * @test
     */
    public function it_translates_command_to_and_from_message()
    {
        $messageTranslator = new MessageTranslator();

        $doSomething = DoSomething::fromData('test command');

        $this->assertTrue($messageTranslator->canTranslateToMessage($doSomething));

        $message = $messageTranslator->translateToMessage($doSomething);

        $this->assertEquals(array('data' => 'test command'), $message->payload());

        $command = $messageTranslator->translateFromMessage($message);

        $this->assertInstanceOf('Prooph\ServiceBus\Command', $command);
        $this->assertEquals($doSomething->getMessageName(), $command->getMessageName());
        $this->assertEquals($doSomething->payload(), $command->payload());
        $this->assertEquals($doSomething->uuid()->toString(), $command->uuid()->toString());
        $this->assertEquals($doSomething->version(), $command->version());
        $this->assertEquals($doSomething->createdOn()->format(\DateTime::ISO8601), $command->createdOn()->format(\DateTime::ISO8601));
    }

    /**
     * @test
     */
    public function it_translates_event_to_and_from_message()
    {
        $messageTranslator = new MessageTranslator();

        $somethingDone = SomethingDone::fromData('test event');

        $this->assertTrue($messageTranslator->canTranslateToMessage($somethingDone));

        $message = $messageTranslator->translateToMessage($somethingDone);

        $this->assertEquals(array('data' => 'test event'), $message->payload());

        $event = $messageTranslator->translateFromMessage($message);

        $this->assertInstanceOf('Prooph\ServiceBus\Event', $event);
        $this->assertEquals($somethingDone->getMessageName(), $event->getMessageName());
        $this->assertEquals($somethingDone->payload(), $event->payload());
        $this->assertEquals($somethingDone->uuid()->toString(), $event->uuid()->toString());
        $this->assertEquals($somethingDone->version(), $event->version());
        $this->assertEquals($somethingDone->occurredOn()->format(\DateTime::ISO8601), $event->occurredOn()->format(\DateTime::ISO8601));
    }
}
 