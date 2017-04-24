<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\DefaultListenerHandler;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\InvokeStrategy\OnEventStrategy;
use Prooph\ServiceBus\Plugin\Router\EventRouter;
use ProophTest\ServiceBus\Mock\CustomInvokableMessageHandler;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\CustomMessageEventHandler;
use Prophecy\Argument;

class OnEventStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_invokes_the_on_event_method_of_the_handler(): void
    {
        $eventBus = new EventBus();

        $onEventStrategy = new OnEventStrategy();
        $onEventStrategy->attachToMessageBus($eventBus);

        $onEventHandler = new CustomMessageEventHandler();

        $eventRouter = new EventRouter([
            'ProophTest\ServiceBus\Mock\CustomMessage' => $onEventHandler,
        ]);
        $eventRouter->attachToMessageBus($eventBus);

        $customEvent = new CustomMessage('I am an event');
        $eventBus->dispatch($customEvent);

        $this->assertSame($customEvent, $onEventHandler->getLastMessage());
        $this->assertSame(1, $onEventHandler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_can_be_attached_to_event_bus(): void
    {
        $onEventStrategy = new OnEventStrategy();

        $bus = $this->prophesize(EventBus::class);
        $bus->attach(Argument::type('string'), Argument::type('callable'), Argument::type('integer'))
            ->shouldBeCalled()
            ->willReturn(
                new DefaultListenerHandler(
                    function () {
                    }
                )
            );

        $onEventStrategy->attachToMessageBus($bus->reveal());
    }

    /**
     * @test
     */
    public function it_should_not_handle_already_processed_messages(): void
    {
        $eventBus = new EventBus();

        $onEventStrategy = new OnEventStrategy();
        $onEventStrategy->attachToMessageBus($eventBus);

        $callableHandler = new CustomInvokableMessageHandler();

        $eventRouter = new EventRouter([
            'ProophTest\ServiceBus\Mock\CustomMessage' => $callableHandler,
        ]);
        $eventRouter->attachToMessageBus($eventBus);

        $customEvent = new CustomMessage('I am an event');
        $eventBus->dispatch($customEvent);

        $this->assertSame($customEvent, $callableHandler->getLastMessage());
        $this->assertSame(1, $callableHandler->getInvokeCounter());
    }
}
