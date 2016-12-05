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

namespace ProophTest\ServiceBus;

use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\ServiceBus\MessageBus;
use ProophTest\ServiceBus\Mock\CustomMessageBus;

class MessageBusTest extends TestCase
{
    /**
     * @test
     */
    public function it_attaches_action_event_emitter(): void
    {
        $actionEventEmitter = new ProophActionEventEmitter();

        $messageBus = new CustomMessageBus();
        $messageBus->setActionEventEmitter($actionEventEmitter);

        $this->assertSame($actionEventEmitter, $messageBus->getActionEventEmitter());
    }

    /**
     * @test
     */
    public function it_uses_message_class_as_name_if_no_one_was_set(): void
    {
        $messageBus = new CustomMessageBus();
        $messageBus->dispatch(new \stdClass());

        $this->assertSame(\stdClass::class, $messageBus->getActionEvent()->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME));
    }

    /**
     * @test
     */
    public function it_uses_message_as_message_name_if_message_is_a_string(): void
    {
        $messageBus = new CustomMessageBus();
        $messageBus->dispatch('message and a message name');

        $this->assertSame('message and a message name', $messageBus->getActionEvent()->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME));
    }

    /**
     * @test
     */
    public function it_uses_type_of_message_as_message_name_if_message_is_neither_object_nor_string(): void
    {
        $messageBus = new CustomMessageBus();
        $messageBus->dispatch([]);

        $this->assertSame('array', $messageBus->getActionEvent()->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME));
    }

    /**
     * @test
     */
    public function it_attaches_custom_event_name(): void
    {
        $messageBus = new CustomMessageBus();
        $messageBus->getActionEventEmitter()->attachListener(CustomMessageBus::EVENT_FOO, function () {
        });
    }

    /**
     * @test
     */
    public function it_does_not_attach_to_invalid_event_names(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown event name given: invalid');

        $messageBus = new CustomMessageBus(['foo']);
        $messageBus->getActionEventEmitter()->attachListener('invalid', function () {
        });
    }
}
