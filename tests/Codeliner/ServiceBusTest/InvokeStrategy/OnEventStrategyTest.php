<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:51
 */

namespace Codeliner\ServiceBusTest\InvokeStrategy;

use Codeliner\ServiceBus\InvokeStrategy\OnEventStrategy;
use Codeliner\ServiceBusTest\Mock\OnEventHandler;
use Codeliner\ServiceBusTest\Mock\SomethingDone;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class OnEventStrategyTest
 *
 * @package Codeliner\ServiceBusTest\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
 