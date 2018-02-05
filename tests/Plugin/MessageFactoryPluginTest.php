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
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\Plugin\MessageFactoryPlugin;
use ProophTest\ServiceBus\Mock\DoSomething;
use Prophecy\Argument;

class MessageFactoryPluginTest extends TestCase
{
    /**
     * @test
     */
    public function it_turns_a_message_given_as_array_into_a_message_object_using_a_factory(): void
    {
        $commandBus = new CommandBus();
        $messageFactory = $this->prophesize(MessageFactory::class);

        $messageFactory->createMessageFromArray('custom-message', Argument::any())->will(function ($args): DoSomething {
            list($messageName, $messageArr) = $args;

            return new DoSomething($messageArr['payload']);
        });

        $factoryPlugin = new MessageFactoryPlugin($messageFactory->reveal());
        $factoryPlugin->attachToMessageBus($commandBus);

        $handled = false;

        $handler = function (DoSomething $command) use (&$handled): void {
            $handled = true;
        };

        $commandBus->attach(
            CommandBus::EVENT_DISPATCH,
            function (ActionEvent $event) use (&$handler): void {
                $event->setParam(CommandBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);
            },
            CommandBus::PRIORITY_ROUTE
        );

        $commandBus->attach(
            CommandBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent): void {
                $message = $actionEvent->getParam(CommandBus::EVENT_PARAM_MESSAGE);
                $this->assertInstanceOf(DoSomething::class, $message);
                $this->assertEquals(['some data'], $message->payload());
            }
        );

        $commandBus->dispatch([
            'message_name' => 'custom-message',
            'payload' => ['some data'],
        ]);

        $this->assertTrue($handled);
    }

    /**
     * @test
     * @group by
     */
    public function it_will_return_early_if_message_name_not_present_in_message(): void
    {
        $commandBus = new CommandBus();
        $messageFactory = $this->prophesize(MessageFactory::class);

        $factoryPlugin = new MessageFactoryPlugin($messageFactory->reveal());
        $factoryPlugin->attachToMessageBus($commandBus);

        $commandBus->attach(
            CommandBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent): void {
                $message = $actionEvent->getParam(CommandBus::EVENT_PARAM_MESSAGE);
                $this->assertEquals(
                    [
                        'payload' => ['some data'],
                    ],
                    $message);
            },
            1000
        );

        try {
            $commandBus->dispatch([
                'payload' => ['some data'],
            ]);
        } catch (MessageDispatchException $exception) {
            // ignore
        }
    }
}
