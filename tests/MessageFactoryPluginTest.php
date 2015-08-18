<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 8/2/15 - 10:31 PM
 */
namespace Prooph\ServiceBusTest;

use Prooph\Common\Event\DefaultActionEvent;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\MessageFactoryPlugin;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prophecy\Argument;

final class MessageFactoryPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_turns_a_message_given_as_array_into_a_message_object_using_a_factory()
    {
        $messageFactory = $this->prophesize(MessageFactory::class);

        $messageFactory->createMessageFromArray("custom-message", Argument::any())->will(function ($args) {
            list($messageName, $messageArr) = $args;

            return new DoSomething($messageArr['payload']);
        });

        $factoryPlugin = new MessageFactoryPlugin($messageFactory->reveal());

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_INITIALIZE, new CommandBus(), [
            //We provide message as array containing a "message_name" key because only in this case the factory plugin
            //gets active
            MessageBus::EVENT_PARAM_MESSAGE => [
                'message_name' => 'custom-message',
                'payload' => ["some data"]
            ]
        ]);

        $factoryPlugin($actionEvent);

        $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);

        $this->assertInstanceOf(DoSomething::class, $message);
        $this->assertEquals(["some data"], $message->payload());
    }
}
