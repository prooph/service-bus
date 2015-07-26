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
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;

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
        $this->trackHandler($events->attachListener(CommandBus::EVENT_LOCATE_HANDLER, array($this, 'onLocateCommandHandler')));
        $this->trackHandler($events->attachListener(EventBus::EVENT_LOCATE_LISTENER, array($this, 'onLocateEventListener')));
        $this->trackHandler($events->attachListener(QueryBus::EVENT_LOCATE_FINDER, array($this, 'onLocateFinder')));
    }

    public function onLocateCommandHandler(ActionEvent $actionEvent)
    {
        $commandHandlerAlias = $actionEvent->getParam(CommandBus::EVENT_PARAM_COMMAND_HANDLER);

        if (is_string($commandHandlerAlias) && $this->serviceLocator->has($commandHandlerAlias)) {
            $actionEvent->setParam(CommandBus::EVENT_PARAM_COMMAND_HANDLER, $this->serviceLocator->get($commandHandlerAlias));
        }
    }

    public function onLocateEventListener(ActionEvent $actionEvent)
    {
        $eventListenerAlias = $actionEvent->getParam(EventBus::EVENT_PARAM_CURRENT_EVENT_LISTENER);

        if (is_string($eventListenerAlias) && $this->serviceLocator->has($eventListenerAlias)) {
            $actionEvent->setParam(EventBus::EVENT_PARAM_CURRENT_EVENT_LISTENER, $this->serviceLocator->get($eventListenerAlias));
        }
    }

    public function onLocateFinder(ActionEvent $actionEvent)
    {
        $finderAlias = $actionEvent->getParam(QueryBus::EVENT_PARAM_FINDER);

        if (is_string($finderAlias) && $this->serviceLocator->has($finderAlias)) {
            $actionEvent->setParam(QueryBus::EVENT_PARAM_FINDER, $this->serviceLocator->get($finderAlias));
        }
    }
}
 