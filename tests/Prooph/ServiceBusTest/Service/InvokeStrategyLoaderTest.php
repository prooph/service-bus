<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 23:19
 */

namespace Prooph\ServiceBusTest\Service;

use Prooph\ServiceBus\Service\InvokeStrategyLoader;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class InvokeStrategyLoaderTest
 *
 * @package Prooph\ServiceBusTest\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class InvokeStrategyLoaderTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_a_callback_strategy()
    {
        $invokeStrategyLoader = new InvokeStrategyLoader();

        $callbackStrategy = $invokeStrategyLoader->get('callback_strategy');

        $this->assertInstanceOf('Prooph\ServiceBus\InvokeStrategy\CallbackStrategy', $callbackStrategy);
    }

    /**
     * @test
     */
    public function it_returns_a_handle_command_strategy()
    {
        $invokeStrategyLoader = new InvokeStrategyLoader();

        $handleCommandStrategy = $invokeStrategyLoader->get('handle_command_strategy');

        $this->assertInstanceOf('Prooph\ServiceBus\InvokeStrategy\HandleCommandStrategy', $handleCommandStrategy);
    }

    /**
     * @test
     */
    public function it_returns_a_on_event_strategy()
    {
        $invokeStrategyLoader = new InvokeStrategyLoader();

        $onEventStrategy = $invokeStrategyLoader->get('on_event_strategy');

        $this->assertInstanceOf('Prooph\ServiceBus\InvokeStrategy\OnEventStrategy', $onEventStrategy);
    }
}
 