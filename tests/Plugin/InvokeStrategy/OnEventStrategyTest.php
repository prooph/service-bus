<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 03/11/14 - 21:51
 */

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use Prooph\ServiceBus\Plugin\InvokeStrategy\OnEventStrategy;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\CustomMessageWithName;
use ProophTest\ServiceBus\Mock\MessageHandler;
use ProophTest\ServiceBus\TestCase;

/**
 * Class OnEventStrategyTest
 *
 * @package ProophTest\ServiceBus\Plugin\InvokeStrategy
 * @author Alexander Miertsch <contact@prooph.de>
 */
class OnEventStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_invokes_the_on_event_method_of_the_handler()
    {
        $onEventStrategy = new OnEventStrategy();

        $customEvent = new CustomMessage("I am an event");

        $onEventHandler = new MessageHandler();

        $this->assertTrue($onEventStrategy->canInvoke($onEventHandler, $customEvent));

        $onEventStrategy->invoke($onEventHandler, $customEvent);

        $this->assertSame($customEvent, $onEventHandler->getLastMessage());
    }

    /**
     * @test
     */
    public function it_determines_the_event_name_from_message_name_call_if_event_has_one()
    {
        $onEventStrategy = new OnEventStrategy();
        $customEvent = new CustomMessageWithName("I am an event with a messageName() method");

        $closure = function ($event) {
            return $this->determineEventName($event);
        };
        $determineEventName = $closure->bindTo($onEventStrategy, $onEventStrategy);

        $this->assertSame('CustomMessageWithSomeOtherName', $determineEventName($customEvent));
    }
}
