<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 22:48
 */

namespace Codeliner\ServiceBusTest\InvokeStrategy;

use Codeliner\ServiceBus\InvokeStrategy\CallbackStrategy;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class CallbackStrategyTest
 *
 * @package Codeliner\ServiceBusTest\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
