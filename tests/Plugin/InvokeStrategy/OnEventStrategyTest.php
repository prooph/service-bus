<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use Prooph\ServiceBus\Plugin\InvokeStrategy\OnEventStrategy;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\MessageHandler;
use ProophTest\ServiceBus\TestCase;

class OnEventStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_invokes_the_on_event_method_of_the_handler(): void
    {
        $onEventStrategy = new OnEventStrategy();

        $customEvent = new CustomMessage("I am an event");

        $onEventHandler = new MessageHandler();

        $onEventStrategy->invoke($onEventHandler, $customEvent);

        $this->assertSame($customEvent, $onEventHandler->getLastMessage());
    }
}
