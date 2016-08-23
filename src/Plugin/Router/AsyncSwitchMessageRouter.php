<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 2016-08-11T08:00:00Z
 */

namespace Prooph\ServiceBus\Plugin\Router;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\Exception;
use Prooph\ServiceBus\MessageBus;

/**
 * Class AsyncSwitchMessageRouter
 *
 * @package Prooph\ServiceBus\Router
 * @author Guy Radford <guyr@crazylime.co.uk>
 */
class AsyncSwitchMessageRouter implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var ActionEventListenerAggregate
     */
    protected $router;

    /**
     * @var MessageProducer
     */
    protected $asyncMessageProducer;


    /**
     * @param ActionEventListenerAggregate $router
     * @param MessageProducer $asyncMessageProducer
     */
    public function __construct(ActionEventListenerAggregate $router, MessageProducer $asyncMessageProducer)
    {
        $this->router = $router;
        $this->asyncMessageProducer = $asyncMessageProducer;
    }

    /**
     * @param ActionEventEmitter $events
     * @return void
     */
    public function attach(ActionEventEmitter $events)
    {
        $this->trackHandler($events->attachListener(MessageBus::EVENT_ROUTE, [$this, "onRouteMessage"]));
    }


    /**
     * @param ActionEvent $actionEvent
     */
    public function onRouteMessage(ActionEvent $actionEvent)
    {

        $messageName = (string)$actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);


        if (is_object($message) && $message instanceof Message)

        $messageMetadata = $message->metadata();

        //if the message is marked as async, but had not yet been sent via async then send to async producer
        if ($message instanceof AsyncMessage && !(isset($messageMetadata['handled-by-async-queue']) && $messageMetadata['handled-by-async-queue'] === true)){

            //apply meta data, this is need to we can identify that the message has already been send via the async bus
            $message = $message->withAddedMetadata('handled-by-async-queue', true);

            // TODO: do I need to re add the message back into the ActionEvent?
//            $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE, $message);

            $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $this->asyncMessageProducer);

            return;
        }


        // pass ActionEvent to decorated router
        return $this->router->onRouteMessage($actionEvent);

    }
}
