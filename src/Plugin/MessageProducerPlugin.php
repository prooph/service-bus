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

namespace Prooph\ServiceBus\Plugin;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;

/**
 * If the MessageProducerPlugin is attached to a message bus it routes all messages
 * to the Prooph\ServiceBus\Async\MessageProducer it is initialized with.
 */
class MessageProducerPlugin extends AbstractPlugin
{
    /**
     * @var MessageProducer
     */
    private $messageProducer;

    public function __construct(MessageProducer $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $event): void {
                $bus = $event->getTarget();

                if ($bus instanceof EventBus) {
                    $listeners = $event->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, []);
                    $listeners[] = $this->messageProducer;
                    $event->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $listeners);
                } else {
                    $event->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $this->messageProducer);
                }
            },
            MessageBus::PRIORITY_INITIALIZE
        );
    }
}
