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
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\MessageBus;

/**
 * This plugin listens on the MessageBus::EVENT_DISPATCH action event with MessageBus::PRIORITY_INITIALIZE.
 * It checks if the message of the action event is given as an array and
 * if the array contains a key "message_name".
 * If both conditions are met the plugin uses the injected Prooph\Common\Messaging\MessageFactory
 * to translate the message array into a Prooph\Common\Messaging\Message
 */
class MessageFactoryPlugin extends AbstractPlugin
{
    /**
     * @var MessageFactory
     */
    private $messageFactory;

    public function __construct(MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);

                if (! is_array($message)) {
                    return;
                }

                if (! array_key_exists('message_name', $message)) {
                    return;
                }

                $messageName = $message['message_name'];
                unset($message['message_name']);

                $message = $this->messageFactory->createMessageFromArray($messageName, $message);

                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE, $message);
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, $messageName);
            },
            MessageBus::PRIORITY_INITIALIZE
        );
    }
}
