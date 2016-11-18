<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus\Plugin;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;

/**
 * Class MessageProducerPlugin
 *
 * If the MessageProducerPlugin is attached to a message bus it routes all messages
 * to the Prooph\ServiceBus\Async\MessageProducer it is initialized with.
 *
 * @package Prooph\ServiceBus\Plugin
 */
final class MessageProducerPlugin implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var MessageProducer
     */
    private $messageProducer;

    public function __construct(MessageProducer $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    public function attach(ActionEventEmitter $emitter): void
    {
        $this->trackHandler($emitter->attachListener(MessageBus::EVENT_INITIALIZE, [$this, 'onDispatchInitialize']));
    }

    public function onDispatchInitialize(ActionEvent $event): void
    {
        $bus = $event->getTarget();

        if ($bus instanceof EventBus) {
            $listeners = $event->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, []);
            $listeners[] = $this->messageProducer;
            $event->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $listeners);
        } else {
            $event->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $this->messageProducer);
        }
    }
}
