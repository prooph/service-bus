<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 23.09.14 - 20:56
 */

namespace Prooph\ServiceBus\Plugin;

use Interop\Container\ContainerInterface;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\MessageBus;

/**
 * Class ServiceLocatorPlugin
 *
 * This plugin can be used to lazy load message handlers.
 * Initialize it with a Interop\Container\ContainerInterface
 * and route your messages to the service id only.
 *
 * @package Prooph\ServiceBus\ServiceLocator
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ServiceLocatorPlugin implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;

    /**
     * @param ContainerInterface $serviceLocator
     */
    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
    /**
     * @param ActionEventEmitter $events
     *
     * @return void
     */
    public function attach(ActionEventEmitter $events)
    {
        $this->trackHandler($events->attachListener(MessageBus::EVENT_LOCATE_HANDLER, array($this, 'onLocateMessageHandler')));
    }

    public function onLocateMessageHandler(ActionEvent $actionEvent)
    {
        $messageHandlerAlias = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);

        if (is_string($messageHandlerAlias) && $this->serviceLocator->has($messageHandlerAlias)) {
            $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $this->serviceLocator->get($messageHandlerAlias));
        }
    }
}
 