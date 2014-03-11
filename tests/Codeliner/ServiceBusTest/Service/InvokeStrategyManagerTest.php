<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 23:19
 */

namespace Codeliner\ServiceBusTest\Service;

use Codeliner\ServiceBus\Service\InvokeStrategyManager;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class InvokeStrategyManagerTest
 *
 * @package Codeliner\ServiceBusTest\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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

        $this->assertInstanceOf('Codeliner\ServiceBus\InvokeStrategy\CallbackStrategy', $callbackStrategy);
    }

    /**
     * @test
     */
    public function it_returns_a_handle_command_strategy()
    {
        $invokeStrategyManager = new InvokeStrategyManager();

        $handleCommandStrategy = $invokeStrategyManager->get('handle_command_strategy');

        $this->assertInstanceOf('Codeliner\ServiceBus\InvokeStrategy\HandleCommandStrategy', $handleCommandStrategy);
    }

    /**
     * @test
     */
    public function it_returns_a_on_event_strategy()
    {
        $invokeStrategyManager = new InvokeStrategyManager();

        $onEventStrategy = $invokeStrategyManager->get('on_event_strategy');

        $this->assertInstanceOf('Codeliner\ServiceBus\InvokeStrategy\OnEventStrategy', $onEventStrategy);
    }
}
 