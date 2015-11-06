<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 11/06/15 - 3:02 PM
 */

namespace ProophTest\ServiceBus;

use Prooph\Common\Event\ActionEventEmitter;
use ProophTest\ServiceBus\Mock\CustomMessageBus;

/**
 * Class MessageBusTest
 * @package ProophTest\ServiceBus
 */
final class MessageBusTest extends TestCase
{
    /**
     * @test
     */
    public function it_attaches_action_event_emitter()
    {
        $actionEventEmitter = $this->prophesize(ActionEventEmitter::class);
        $mock = $actionEventEmitter->reveal();

        $messageBus = new CustomMessageBus();
        $messageBus->setActionEventEmitter($mock);

        $this->assertSame($mock, $messageBus->getActionEventEmitter());
    }
}
