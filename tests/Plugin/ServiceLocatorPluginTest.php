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

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;
use ProophTest\ServiceBus\Mock\MessageHandler;

class ServiceLocatorPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_locates_a_service_using_the_message_handler_param_of_the_action_event(): void
    {
        $handler = new MessageHandler();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('custom-handler')->willReturn(true)->shouldBeCalled();

        $container->get('custom-handler')->willReturn($handler)->shouldBeCalled();

        $commandBus = new CommandBus();

        $locatorPlugin = new ServiceLocatorPlugin($container->reveal());
        $locatorPlugin->attachToMessageBus($commandBus);

        $commandBus->attach(
            CommandBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $actionEvent->setParam(CommandBus::EVENT_PARAM_MESSAGE_HANDLER, 'custom-handler');
            },
            CommandBus::PRIORITY_INITIALIZE
        );

        $commandBus->dispatch('foo');
    }
}
