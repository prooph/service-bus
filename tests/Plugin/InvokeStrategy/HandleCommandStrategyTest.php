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

use PHPUnit\Framework\TestCase;
use Prooph\ServiceBus\Plugin\InvokeStrategy\HandleCommandStrategy;
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
        $handleCommandStrategy = new HandleCommandStrategy();

        $doSomething = new CustomMessage('I am a command');

        $handleCommandHandler = new MessageHandler();

        $handleCommandStrategy->invoke($handleCommandHandler, $doSomething);

        $this->assertSame($doSomething, $handleCommandHandler->getLastMessage());
    }

    /**
     * @test
     */
    public function it_invokes_the_handle_command_method_of_the_handler_without_command_name(): void
    {
        $handleCommandStrategy = new HandleCommandStrategy();

        $doSomething = new CustomMessage('I am a command');

        $handleCommandHandler = new CustomMessageCommandHandler();

        $handleCommandStrategy->invoke($handleCommandHandler, $doSomething);

        $this->assertSame($doSomething, $handleCommandHandler->getLastMessage());
    }
}
