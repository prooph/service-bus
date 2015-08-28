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

namespace Prooph\ServiceBusTest\Plugin\InvokeStrategy;

use Prooph\ServiceBus\Plugin\InvokeStrategy\OnEventStrategy;
use Prooph\ServiceBusTest\Mock\CustomMessage;
use Prooph\ServiceBusTest\Mock\MessageHandler;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class OnEventStrategyTest
 *
 * @package Prooph\ServiceBusTest\InvokeStrategy
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
}
