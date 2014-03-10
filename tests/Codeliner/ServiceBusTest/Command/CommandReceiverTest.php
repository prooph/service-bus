<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 00:02
 */

namespace Codeliner\ServiceBusTest\Command;

use Codeliner\ServiceBus\Command\CommandReceiver;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class CommandReceiverTest
 *
 * @package Codeliner\ServiceBusTest\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandReceiverTest extends TestCase
{
    /**
     * @var CommandReceiver
     */
    private $commandReceiver;

    public $commandHeader;

    public $commandPayload = null;

    protected function setUp()
    {
        $commandHandlerLocator = new ServiceBusManager();

        $this->commandHeader  = null;
        $this->commandPayload = null;

        $self = $this;

        $commandHandlerLocator->setService('test-case-callback', function (DoSomething $aCommand) use ($self) {

            $commandHeader = new MessageHeader(
                $aCommand->uuid(),
                $aCommand->createdOn(),
                $aCommand->version(),
                'test-case'
            );

            $self->commandHeader  = $commandHeader;
            $self->commandPayload = $aCommand->payload();
        });

        $this->commandReceiver = new CommandReceiver(
            array(
                'Codeliner\ServiceBusTest\Mock\DoSomething' => 'test-case-callback'
            ),
            $commandHandlerLocator
        );
    }

    /**
     * @test
     */
    public function it_handles_a_message_and_calls_the_related_command_on_configured_handler()
    {
        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case');

        $message = new StandardMessage(
            'Codeliner\ServiceBusTest\Mock\DoSomething',
            $header,
            array('data' => 'test')
        );

        $this->commandReceiver->handle($message);

        $this->assertTrue($header->sameHeaderAs($this->commandHeader));
        $this->assertEquals(array('data' => 'test'), $this->commandPayload);
    }
}
 