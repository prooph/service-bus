<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/29/15 - 13:59
 */

namespace Prooph\ServiceBusTest\Plugin\InvokeStrategy;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ListenerHandler;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\InvokeStrategy\AbstractInvokeStrategy;

/**
 * Class AbstractInvokeStrategyTest
 * @package Prooph\ServiceBusTest\Plugin\InvokeStrategy
 */
final class AbstractInvokeStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_attached_listener_to_event_and_tracks_it()
    {
        $strategy = $this->getMockForAbstractClass(AbstractInvokeStrategy::class);

        $listenerHandlerMock = $this->getMockForAbstractClass(ListenerHandler::class);

        $actionEventEmitterMock = $this->getMockForAbstractClass(ActionEventEmitter::class);
        $actionEventEmitterMock
            ->expects($this->once())
            ->method('attachListener')
            ->with(MessageBus::EVENT_INVOKE_HANDLER, $strategy, 0)
            ->will($this->returnValue($listenerHandlerMock));

        $strategy->attach($actionEventEmitterMock);
    }

    /**
     * @test
     */
    public function it_fetches_message_and_handler_and_invokes_them_it_possible()
    {
        $actionEventMock = $this->getMockForAbstractClass(ActionEvent::class);
        $actionEventMock
            ->expects($this->at(0))
            ->method('getParam')
            ->with(MessageBus::EVENT_PARAM_MESSAGE)
            ->will($this->returnValue('message'));

        $actionEventMock
            ->expects($this->at(1))
            ->method('getParam')
            ->with(MessageBus::EVENT_PARAM_MESSAGE_HANDLER)
            ->will($this->returnValue('handler'));

        $strategy = $this->getMockForAbstractClass(AbstractInvokeStrategy::class);
        $strategy
            ->expects($this->once())
            ->method('canInvoke')
            ->with('handler', 'message')
            ->will($this->returnValue(true));

        $strategy
            ->expects($this->once())
            ->method('invoke')
            ->with('handler', 'message');

        $strategy($actionEventMock);
    }
}
