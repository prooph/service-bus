<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2021 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin\Router;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\Async\AsyncMessage;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;
use Prooph\ServiceBus\QueryBus;

class AsyncSwitchMessageRouter extends AbstractPlugin implements MessageBusRouterPlugin
{
    /**
     * @var MessageBusRouterPlugin
     */
    protected $router;

    /**
     * @var MessageProducer
     */
    protected $asyncMessageProducer;

    public function __construct(MessageBusRouterPlugin $router, MessageProducer $asyncMessageProducer)
    {
        $this->router = $router;
        $this->asyncMessageProducer = $asyncMessageProducer;
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            [$this, 'onRouteMessage'],
            MessageBus::PRIORITY_ROUTE
        );
    }

    public function onRouteMessage(ActionEvent $actionEvent): void
    {
        $messageName = (string) $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);

        //if the message is marked with AsyncMessage, but had not yet been sent via async then sent to async producer
        if ($message instanceof AsyncMessage && ! (isset($message->metadata()['handled-async']) && $message->metadata()['handled-async'] === true)) {
            //apply meta data, this is need to we can identify that the message has already been send via the async producer
            $message = $message->withAddedMetadata('handled-async', true);

            // update ActionEvent
            $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE, $message);

            if ($actionEvent->getTarget() instanceof CommandBus || $actionEvent->getTarget() instanceof QueryBus) {
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $this->asyncMessageProducer);
            } elseif ($actionEvent->getTarget() instanceof EventBus) {
                //Target is an event bus so we set message producer as the only listener of the message
                $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, [$this->asyncMessageProducer]);
            } else {
                throw new RuntimeException('Unexpected bus implementation. This plugin is only compatible with standard CommandBus, QueryBus and EventBus implementations.');
            }

            return;
        }

        // pass ActionEvent to decorated router
        $this->router->onRouteMessage($actionEvent);
    }
}
