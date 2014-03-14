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

use Codeliner\ServiceBus\Command\CommandFactory;
use Codeliner\ServiceBus\Command\CommandReceiver;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;
use Zend\EventManager\Event;
use Zend\EventManager\EventInterface;

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
                'test-case',
                MessageHeader::TYPE_COMMAND
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
        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $message = new StandardMessage(
            'Codeliner\ServiceBusTest\Mock\DoSomething',
            $header,
            array('data' => 'test')
        );

        $this->commandReceiver->handle($message);

        $this->assertTrue($header->sameHeaderAs($this->commandHeader));
        $this->assertEquals(array('data' => 'test'), $this->commandPayload);
    }

    /**
     * @test
     */
    public function it_triggers_all_related_events()
    {
        $preHandleTriggered         = false;
        $preInvokeHandlerTriggered  = false;
        $postInvokeHandlerTriggered = false;
        $postHandleTriggered        = false;


        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case', MessageHeader::TYPE_COMMAND);

        $message = new StandardMessage(
            'Codeliner\ServiceBusTest\Mock\DoSomething',
            $header,
            array('data' => 'test')
        );

        $commandFactory = new CommandFactory();

        $command = $commandFactory->fromMessage($message);

        $this->commandReceiver->events()->attach(
            'handle.pre',
            function (EventInterface $e) use (&$preHandleTriggered, $message) {
                $this->assertSame($message, $e->getParam('message'));
                $preHandleTriggered = true;
            }
        );

        $this->commandReceiver->events()->attach(
            'invoke_handler.pre',
            function (EventInterface $e) use (&$preInvokeHandlerTriggered, $command) {
                $this->assertSame($command->uuid(), $e->getParam('command')->uuid());
                $this->assertTrue(is_callable($e->getParam('handler')));
                $preInvokeHandlerTriggered = true;
            }
        );

        $this->commandReceiver->events()->attach(
            'invoke_handler.post',
            function (EventInterface $e) use (&$postInvokeHandlerTriggered, $command) {
                $this->assertSame($command->uuid(), $e->getParam('command')->uuid());
                $this->assertTrue(is_callable($e->getParam('handler')));
                $postInvokeHandlerTriggered = true;
            }
        );

        $this->commandReceiver->events()->attach(
            'handle.post',
            function (EventInterface $e) use (&$postHandleTriggered, $command, $message) {
                $this->assertSame($command->uuid(), $e->getParam('command')->uuid());
                $this->assertSame($message, $e->getParam('message'));
                $this->assertTrue(is_callable($e->getParam('handler')));
                $postHandleTriggered = true;
            }
        );

        $this->commandReceiver->handle($message);

        $this->assertTrue($preHandleTriggered);
        $this->assertTrue($preInvokeHandlerTriggered);
        $this->assertTrue($postInvokeHandlerTriggered);
        $this->assertTrue($postHandleTriggered);
    }
}
 