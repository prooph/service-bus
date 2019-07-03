<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2019 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Plugin\InvokeStrategy\HandleCommandStrategy;
use ProophTest\ServiceBus\Mock\CustomInvokableMessageHandler;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\CustomMessageCommandHandler;
use ProophTest\ServiceBus\Mock\MessageHandler;

class HandleCommandStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_invokes_the_handle_command_method_of_the_handler(): void
    {
        $commandHandler = new MessageHandler();

        $commandBus = new CommandBus();
        $commandBus->attach(
            CommandBus::EVENT_DISPATCH,
            function (ActionEvent $event) use ($commandHandler): void {
                $event->setParam(CommandBus::EVENT_PARAM_MESSAGE_HANDLER, $commandHandler);
            },
            CommandBus::PRIORITY_ROUTE
        );

        $handleCommandStrategy = new HandleCommandStrategy();
        $handleCommandStrategy->attachToMessageBus($commandBus);

        $doSomething = new CustomMessage('I am a command');

        $commandBus->dispatch($doSomething);

        $this->assertSame($doSomething, $commandHandler->getLastMessage());
        $this->assertSame(1, $commandHandler->getInvokeCounter());
    }

    /**
     * @test
     */
    public function it_invokes_the_handle_command_method_of_the_handler_without_command_name(): void
    {
        $commandHandler = new CustomMessageCommandHandler();

        $commandBus = new CommandBus();
        $commandBus->attach(
            CommandBus::EVENT_DISPATCH,
            function (ActionEvent $event) use ($commandHandler): void {
                $event->setParam(CommandBus::EVENT_PARAM_MESSAGE_HANDLER, $commandHandler);
            },
            CommandBus::PRIORITY_ROUTE
        );

        $handleCommandStrategy = new HandleCommandStrategy();
        $handleCommandStrategy->attachToMessageBus($commandBus);

        $doSomething = new CustomMessage('I am a command');

        $commandBus->dispatch($doSomething);

        $this->assertSame($doSomething, $commandHandler->getLastMessage());
    }

    /**
     * @test
     */
    public function it_should_not_handle_already_processed_messages(): void
    {
        $commandHandler = new CustomInvokableMessageHandler();

        $commandBus = new CommandBus();
        $commandBus->attach(
            CommandBus::EVENT_DISPATCH,
            function (ActionEvent $event) use ($commandHandler): void {
                $event->setParam(CommandBus::EVENT_PARAM_MESSAGE_HANDLER, $commandHandler);
            },
            CommandBus::PRIORITY_ROUTE
        );

        $handleCommandStrategy = new HandleCommandStrategy();
        $handleCommandStrategy->attachToMessageBus($commandBus);

        $doSomething = new CustomMessage('I am a command');

        $commandBus->dispatch($doSomething);

        $this->assertSame($doSomething, $commandHandler->getLastMessage());
    }
}
