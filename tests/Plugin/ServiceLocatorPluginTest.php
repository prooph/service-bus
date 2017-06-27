<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
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
     * @group by
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
}
