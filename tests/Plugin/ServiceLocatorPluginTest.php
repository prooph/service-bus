<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/02/15 - 10:27 PM
 */
namespace ProophTest\ServiceBus\Plugin;

use Interop\Container\ContainerInterface;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;
use ProophTest\ServiceBus\Mock\MessageHandler;
use ProophTest\ServiceBus\TestCase;

/**
 * Class ServiceLocatorPluginTest
 * @package ProophTest\ServiceBus\Plugin
 */
final class ServiceLocatorPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_locates_a_service_using_the_message_handler_param_of_the_action_event()
    {
        $handler = new MessageHandler();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has("custom-handler")->willReturn(true);

        $container->get("custom-handler")->willReturn($handler);

        $locatorPlugin = new ServiceLocatorPlugin($container->reveal());

        $actionEvent = new DefaultActionEvent(MessageBus::EVENT_LOCATE_HANDLER, new CommandBus(), [
            MessageBus::EVENT_PARAM_MESSAGE_HANDLER => "custom-handler"
        ]);

        $locatorPlugin->onLocateMessageHandler($actionEvent);

        $this->assertSame($handler, $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER));
    }
}
