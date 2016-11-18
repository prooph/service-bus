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

namespace ProophTest\ServiceBus\Plugin;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\MessageFactoryPlugin;
use ProophTest\ServiceBus\Mock\DoSomething;
use Prophecy\Argument;

/**
 * Class MessageFactoryPluginTest
 * @package ProophTest\ServiceBus\Plugin
 */
final class MessageFactoryPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_turns_a_message_given_as_array_into_a_message_object_using_a_factory(): void
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

    /**
     * @test
     */
    public function it_will_return_eary_if_message_name_not_present_in_message(): void
    {
        $messageFactoryMock = $this->getMockForAbstractClass(MessageFactory::class);
        $messageFactoryMock
            ->expects($this->never())
            ->method('createMessageFromArray');

        $actionEventMock = $this->getMockForAbstractClass(ActionEvent::class);
        $actionEventMock
            ->expects($this->once())
            ->method('getParam')
            ->with(MessageBus::EVENT_PARAM_MESSAGE)
            ->will($this->returnValue([]));

        $messagePlugin = new MessageFactoryPlugin($messageFactoryMock);
        $messagePlugin($actionEventMock);
    }
}
