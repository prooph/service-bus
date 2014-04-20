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

use Prooph\ServiceBus\Service\InvokeStrategyManager;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class InvokeStrategyManagerTest
 *
 * @package Prooph\ServiceBusTest\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class InvokeStrategyManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_a_callback_strategy()
    {
        $invokeStrategyManager = new InvokeStrategyManager();

        $callbackStrategy = $invokeStrategyManager->get('callback_strategy');

        $this->assertInstanceOf('Prooph\ServiceBus\InvokeStrategy\CallbackStrategy', $callbackStrategy);
    }

    /**
     * @test
     */
    public function it_returns_a_handle_command_strategy()
    {
        $invokeStrategyManager = new InvokeStrategyManager();

        $handleCommandStrategy = $invokeStrategyManager->get('handle_command_strategy');

        $this->assertInstanceOf('Prooph\ServiceBus\InvokeStrategy\HandleCommandStrategy', $handleCommandStrategy);
    }

    /**
     * @test
     */
    public function it_returns_a_on_event_strategy()
    {
        $invokeStrategyManager = new InvokeStrategyManager();

        $onEventStrategy = $invokeStrategyManager->get('on_event_strategy');

        $this->assertInstanceOf('Prooph\ServiceBus\InvokeStrategy\OnEventStrategy', $onEventStrategy);
    }
}
 