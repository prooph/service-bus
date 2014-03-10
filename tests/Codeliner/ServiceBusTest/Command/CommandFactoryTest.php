<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 21:15
 */

namespace Codeliner\ServiceBusTest\Command;

use Codeliner\ServiceBus\Command\CommandFactory;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class CommandFactoryTest
 *
 * @package Codeliner\ServiceBusTest\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_command_from_message()
    {
        $commandFactory = new CommandFactory();

        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case');

        $message = new StandardMessage(
            'Codeliner\ServiceBusTest\Mock\DoSomething',
            $header,
            array('data' => 'test data')
        );

        $command = $commandFactory->fromMessage($message);

        $commandHeader = new MessageHeader($command->uuid(), $command->createdOn(), $command->version(), 'test-case');

        $this->assertTrue($command instanceof DoSomething);
        $this->assertTrue($commandHeader->sameHeaderAs($header));
        $this->assertEquals('test data', $command->data());
    }
}