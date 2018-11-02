<?php

/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\Router\EventRouter;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;
use ProophTest\ServiceBus\Mock\MessageHandler;
use ProophTest\ServiceBus\Mock\SomethingDone;
use Psr\Container\ContainerInterface;

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

    /**
     * @test
     */
    public function it_doesnt_override_previous_event_handlers(): void
    {
        $handledOne = false;

        $handlerOne = function (SomethingDone $event) use (&$handledOne): void {
            $handledOne = true;
        };

        $handlerTwo = new MessageHandler();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('custom-handler')->willReturn(true)->shouldBeCalled();

        $container->get('custom-handler')->willReturn($handlerTwo)->shouldBeCalled();

        $eventBus = new EventBus();

        $router = new EventRouter();
        $router->route(SomethingDone::class)->to($handlerOne)->andTo('custom-handler');

        $router->attachToMessageBus($eventBus);

        $locatorPlugin = new ServiceLocatorPlugin($container->reveal());

        $locatorPlugin->attachToMessageBus($eventBus);

        $eventBus->dispatch(new SomethingDone(['foo' => 'bar']));

        $this->assertTrue($handledOne);
        $this->assertSame(1, $handlerTwo->getInvokeCounter());
    }

    /**
     * @test
     */
    public function make_sure_servicenames_do_not_end_up_as_listener_instance(): void
    {
        $handlerOne = new MessageHandler();
        $handlerTwo = new MessageHandler();
        $eventBus = new EventBus();
        $router = new EventRouter();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('handler-one')->willReturn(true)->shouldBeCalled();
        $container->get('handler-one')->willReturn($handlerOne)->shouldBeCalled();

        $container->has('handler-two')->willReturn(true)->shouldBeCalled();
        $container->get('handler-two')->willReturn($handlerTwo)->shouldBeCalled();

        $router->route(SomethingDone::class)->to('handler-one');
        $router->route(SomethingDone::class)->to('handler-two');

        $router->attachToMessageBus($eventBus);

        $locatorPlugin = new ServiceLocatorPlugin($container->reveal());

        $locatorPlugin->attachToMessageBus($eventBus);

        $eventBus->attach(EventBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $listeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS);

                $this->assertCount(2, $listeners);
                $this->assertContainsOnly(MessageHandler::class, $listeners);
            }, PHP_INT_MIN);

        $eventBus->dispatch(new SomethingDone(['foo' => 'bar']));
    }
}
