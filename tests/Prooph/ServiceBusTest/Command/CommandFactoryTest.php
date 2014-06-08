<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 21:15
 */

namespace Prooph\ServiceBusTest\Command;

use Prooph\ServiceBus\Command\CommandFactory;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\StandardMessage;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class CommandFactoryTest
 *
 * @package Prooph\ServiceBusTest\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_command_from_message()
    {
        $commandFactory = new CommandFactory();

        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $message = new StandardMessage(
            'Prooph\ServiceBusTest\Mock\DoSomething',
            $header,
            array('data' => 'test data')
        );

        $command = $commandFactory->fromMessage($message);

        $commandHeader = new MessageHeader(
            $command->uuid(),
            $command->createdOn(),
            $command->version(),
            'test-case',
            MessageHeader::TYPE_COMMAND
        );

        $this->assertTrue($command instanceof DoSomething);
        $this->assertTrue($commandHeader->sameHeaderAs($header));
        $this->assertEquals('test data', $command->data());
    }
}