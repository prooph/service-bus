<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 7/26/15 - 10:12 PM
 */
namespace Prooph\ServiceBus\Plugin;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\MessageBus;

/**
 * Class MessageFactoryPlugin
 *
 * This plugin listens on the MessageBus::EVENT_INITIALIZE action event.
 * It checks if the message of the action event is given as an array and
 * if the array contains a key "message_name".
 * If both conditions are met the plugin uses the injected Prooph\Common\Messaging\MessageFactory
 * to translate the message array into a Prooph\Common\Messaging\Message
 *
 * @package Prooph\ServiceBus\Plugin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MessageFactoryPlugin implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @param MessageFactory $messageFactory
     */
    public function __construct(MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param ActionEventEmitter $dispatcher
     */
    public function attach(ActionEventEmitter $dispatcher)
    {
        $this->trackHandler($dispatcher->attachListener(MessageBus::EVENT_INITIALIZE, $this));
    }

    /**
     * @param ActionEvent $actionEvent
     */
    public function __invoke(ActionEvent $actionEvent)
    {
        $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);

        if (! is_array($message)) return;

        if (! array_key_exists('message_name', $message)) return;

        $messageName = $message['message_name'];
        unset($message['message_name']);

        $message = $this->messageFactory->createMessageFromArray($messageName, $message);

        $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE, $message);
        $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, $messageName);
    }
}