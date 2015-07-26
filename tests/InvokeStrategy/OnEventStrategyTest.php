<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:51
 */

namespace Prooph\ServiceBusTest\InvokeStrategy;

use Prooph\ServiceBus\InvokeStrategy\OnEventStrategy;
use Prooph\ServiceBusTest\Mock\OnEventHandler;
use Prooph\ServiceBusTest\Mock\SomethingDone;
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

        $somethingDone = SomethingDone::fromData('test payload');

        $onEventHandler = new OnEventHandler();

        $this->assertTrue($onEventStrategy->canInvoke($onEventHandler, $somethingDone));

        $onEventStrategy->invoke($onEventHandler, $somethingDone);

        $this->assertEquals('test payload', $onEventHandler->lastEvent()->data());
    }
}
 