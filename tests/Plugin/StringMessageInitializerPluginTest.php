<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 20/10/15 - 20:31 PM
 */

namespace ProophTest\ServiceBus\Plugin;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\StringMessageInitializerPlugin;

/**
 * Class StringMessageInitializerPluginTest
 * @package ProophTest\ServiceBus\Plugin
 */
final class StringMessageInitializerPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_attaches_listener_on_emitter()
    {
        $plugin = new StringMessageInitializerPlugin;
        $emitter = $this->prophesize(ActionEventEmitter::class);

        $emitter->attachListener(MessageBus::EVENT_INITIALIZE, [$plugin, 'onInitializeEvent'])->shouldBeCalled();

        $plugin->attach($emitter->reveal());
    }

    /**
     * @test
     */
    public function it_sets_message_name_to_the_message_contents()
    {
        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE)->willReturn('abc');
        $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, 'abc')->shouldBeCalled();

        $plugin = new StringMessageInitializerPlugin;
        $plugin->onInitializeEvent($actionEvent->reveal());
    }

    /**
     * @param mixed $nonStringValue
     * @dataProvider nonStringValues
     * @test
     */
    public function it_will_skip_if_the_argument_it_not_a_string($nonStringValue)
    {
        $actionEvent = $this->prophesize(ActionEvent::class);
        $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE)->willReturn($nonStringValue);
        $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, $nonStringValue)->shouldNotBeCalled();

        $plugin = new StringMessageInitializerPlugin;
        $plugin->onInitializeEvent($actionEvent->reveal());
    }

    /**
     * @return array[]
     */
    public function nonStringValues()
    {
        return [
            [new \stdClass()],
            [[]],
            [1.0],
            [1],
        ];
    }
}
