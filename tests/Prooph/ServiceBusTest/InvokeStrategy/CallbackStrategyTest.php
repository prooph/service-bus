<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 22:48
 */

namespace Prooph\ServiceBusTest\InvokeStrategy;

use Prooph\ServiceBus\InvokeStrategy\CallbackStrategy;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class CallbackStrategyTest
 *
 * @package Prooph\ServiceBusTest\InvokeStrategy
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CallbackStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_invoke_a_callable()
    {
        $callbackStrategy = new CallbackStrategy();

        $this->assertTrue($callbackStrategy->canInvoke(function () {}, DoSomething::fromData(array())));
    }

    /**
     * @test
     */
    public function it_invokes_a_callable()
    {
        $callbackStrategy = new CallbackStrategy();

        $checkData = '';

        $callbackStrategy->invoke(
            function (DoSomething $aCommand) use (&$checkData) {
                $checkData = $aCommand->data();
            },
            DoSomething::fromData('test')
        );

        $this->assertEquals('test', $checkData);
    }
}
