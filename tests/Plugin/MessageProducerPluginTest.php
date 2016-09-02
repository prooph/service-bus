<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ProophTest\ServiceBus\Plugin;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ListenerHandler;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\MessageProducerPlugin;
use ProophTest\ServiceBus\TestCase;

/**
 * Class MessageProducerPluginTest
 *
 * @package ProophTest\ServiceBus\Plugin
 */
final class MessageProducerPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_message_producer_as_message_handler_on_dispatch_initialize()
    {
        $messageProducer = $this->prophesize(MessageProducer::class);
        $commandBus = $this->prophesize(CommandBus::class);
        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEventEmitter = $this->prophesize(ActionEventEmitter::class);
        $listenerHandler = $this->prophesize(ListenerHandler::class);

        $messageProducerPlugin = new MessageProducerPlugin($messageProducer->reveal());

        $actionEventEmitter
            ->attachListener(MessageBus::EVENT_INITIALIZE, [$messageProducerPlugin, 'onDispatchInitialize'])
            ->willReturn($listenerHandler->reveal())
            ->shouldBeCalled();

        $messageProducerPlugin->attach($actionEventEmitter->reveal());

        $actionEvent->getTarget()->willReturn($commandBus->reveal());

        $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $messageProducer->reveal())->shouldBeCalled();

        $messageProducerPlugin->onDispatchInitialize($actionEvent->reveal());
    }

    /**
     * @test
     */
    public function it_adds_message_producer_as_event_listener_on_dispatch_initialize()
    {
        $messageProducer = $this->prophesize(MessageProducer::class);
        $eventBus = $this->prophesize(EventBus::class);
        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEventEmitter = $this->prophesize(ActionEventEmitter::class);
        $listenerHandler = $this->prophesize(ListenerHandler::class);

        $messageProducerPlugin = new MessageProducerPlugin($messageProducer->reveal());

        $actionEventEmitter
            ->attachListener(MessageBus::EVENT_INITIALIZE, [$messageProducerPlugin, 'onDispatchInitialize'])
            ->willReturn($listenerHandler->reveal())
            ->shouldBeCalled();

        $messageProducerPlugin->attach($actionEventEmitter->reveal());

        $actionEvent->getTarget()->willReturn($eventBus->reveal());

        $eventListeners = ['i_am_an_event_listener'];

        $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [])->willReturn($eventListeners);

        //Message Producer should be added to list of event listeners
        $eventListeners[] = $messageProducer->reveal();

        $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $eventListeners)->shouldBeCalled();

        $messageProducerPlugin->onDispatchInitialize($actionEvent->reveal());
    }
}
