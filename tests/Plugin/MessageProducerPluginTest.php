<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\MessageProducerPlugin;
use ProophTest\ServiceBus\Mock\DoSomething;
use ProophTest\ServiceBus\Mock\SomethingDone;
use Prophecy\Argument;

class MessageProducerPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_message_producer_as_message_handler_on_dispatch_initialize(): void
    {
        $command = new DoSomething(['foo' => 'bar']);

        $messageProducer = $this->prophesize(MessageProducer::class);
        $messageProducer->__invoke(Argument::type(DoSomething::class))->shouldBeCalled();
        $commandBus = new CommandBus();

        $messageProducerPlugin = new MessageProducerPlugin($messageProducer->reveal());
        $messageProducerPlugin->attachToMessageBus($commandBus);

        $handler = null;

        $commandBus->attach(
            CommandBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$handler): void {
                $handler = $actionEvent->getParam(CommandBus::EVENT_PARAM_MESSAGE_HANDLER);
            }
        );

        $commandBus->dispatch($command);
        $this->assertSame($messageProducer->reveal(), $handler);
    }

    /**
     * @test
     */
    public function it_adds_message_producer_as_event_listener_on_dispatch_initialize(): void
    {
        $event = new SomethingDone(['foo' => 'bar']);

        $messageProducer = $this->prophesize(MessageProducer::class);
        $eventBus = new EventBus();

        $messageProducerPlugin = new MessageProducerPlugin($messageProducer->reveal());
        $messageProducerPlugin->attachToMessageBus($eventBus);

        $listeners = null;

        $eventBus->attach(
            EventBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent) use (&$listeners): void {
                $listeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS);
            }
        );

        $eventBus->dispatch($event);
        $this->assertSame($messageProducer->reveal(), $listeners[0]);
    }
}
