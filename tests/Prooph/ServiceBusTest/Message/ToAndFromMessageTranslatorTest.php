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

use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\ServiceBus\Message\FromRemoteMessageTranslator;
use Prooph\ServiceBus\Message\ProophDomainMessageToRemoteMessageTranslator;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class ToAndFromMessageTranslatorTest
 *
 * @package Prooph\ServiceBusTest\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class ToAndFromMessageTranslatorTest extends TestCase
{
    /**
     * @test
     */
    public function it_translates_command_to_and_from_message()
    {
        $toMessageTranslator = new ProophDomainMessageToRemoteMessageTranslator();

        $doSomething = DoSomething::fromData('test command');

        $this->assertTrue($toMessageTranslator->canTranslateToRemoteMessage($doSomething));

        $message = $toMessageTranslator->translateToRemoteMessage($doSomething);

        $this->assertEquals(array('data' => 'test command'), $message->payload());

        $fromMessageTranslator = new FromRemoteMessageTranslator();

        $command = $fromMessageTranslator->translateFromRemoteMessage($message);

        $this->assertInstanceOf(Command::class, $command);
        $this->assertEquals($doSomething->messageName(), $command->messageName());
        $this->assertEquals($doSomething->payload(), $command->payload());
        $this->assertEquals($doSomething->uuid()->toString(), $command->uuid()->toString());
        $this->assertEquals($doSomething->version(), $command->version());
        $this->assertEquals($doSomething->createdAt()->format(\DateTime::ISO8601), $command->createdAt()->format(\DateTime::ISO8601));
    }

    /**
     * @test
     */
    public function it_translates_event_to_and_from_message()
    {
        $toMessageTranslator = new ProophDomainMessageToRemoteMessageTranslator();

        $somethingDone = SomethingDone::fromData('test event');

        $this->assertTrue($toMessageTranslator->canTranslateToRemoteMessage($somethingDone));

        $message = $toMessageTranslator->translateToRemoteMessage($somethingDone);

        $this->assertEquals(array('data' => 'test event'), $message->payload());

        $fromMessageTranslator = new FromRemoteMessageTranslator();

        $event = $fromMessageTranslator->translateFromRemoteMessage($message);

        $this->assertInstanceOf(DomainEvent::class, $event);
        $this->assertEquals($somethingDone->messageName(), $event->messageName());
        $this->assertEquals($somethingDone->payload(), $event->payload());
        $this->assertEquals($somethingDone->uuid()->toString(), $event->uuid()->toString());
        $this->assertEquals($somethingDone->version(), $event->version());
        $this->assertEquals($somethingDone->createdAt()->format(\DateTime::ISO8601), $event->createdAt()->format(\DateTime::ISO8601));
    }
}
 