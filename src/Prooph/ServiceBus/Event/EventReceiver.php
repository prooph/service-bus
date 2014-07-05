<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:31
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\EventFactoryLoader;
use Prooph\ServiceBus\Service\InvokeStrategyLoader;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class EventReceiver
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventReceiver implements EventReceiverInterface
{
    /**
     * @var array
     */
    protected $eventMap = array();

    /**
     * @var EventFactoryLoader
     */
    protected $eventFactoryLoader;

    /**
     * @var ServiceLocatorInterface
     */
    protected $eventHandlerLocator;

    /**
     * @var array
     */
    protected $invokeStrategies = array('callback_strategy', 'on_event_strategy');

    /**
     * @var ServiceLocatorInterface
     */
    protected $invokeStrategyLoader;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @param array                   $anEventMap
     * @param ServiceLocatorInterface $anEventHandlerLocator
     */
    public function __construct(array $anEventMap, ServiceLocatorInterface $anEventHandlerLocator)
    {
        $this->eventMap = $anEventMap;
        $this->eventHandlerLocator = $anEventHandlerLocator;
    }

    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return void
     */
    public function handle(MessageInterface $aMessage)
    {
        $results = $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('message' => $aMessage));

        if ($results->stopped()) {
            return;
        }

        if (!isset($this->eventMap[$aMessage->name()])) {
            return;
        }

        $event = $this->getEventFactoryLoader()->get($aMessage->name())->fromMessage($aMessage);

        $eventHandlerAliases = (is_string($this->eventMap[$aMessage->name()]))?
            array($this->eventMap[$aMessage->name()])
            : $this->eventMap[$aMessage->name()];

        foreach ($eventHandlerAliases as $eventHandlerAlias) {

            $handler = $this->eventHandlerLocator->get($eventHandlerAlias);

            $params = compact('event', 'handler');

            $results = $this->events()->trigger('invoke_handler.pre', $this, $params);

            if ($results->stopped()) {
                continue;
            }

            $invokeStrategy = null;

            foreach ($this->getInvokeStrategies() as $invokeStrategyName) {
                $invokeStrategy = $this->getInvokeStrategyLoader()->get($invokeStrategyName);

                if ($invokeStrategy->canInvoke($handler, $event)) {
                    break;
                }

                $invokeStrategy = null;
            }

            if (is_null($invokeStrategy)) {
                throw new RuntimeException(sprintf(
                    'No InvokeStrategy can invoke event %s on handler %s',
                    get_class($event),
                    get_class($handler)
                ));
            }

            $invokeStrategy->invoke($handler, $event);

            $this->events()->trigger('invoke_handler.post', $this, $params);
        }

        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('message' => $aMessage, 'event' => $event));
    }

    /**
     * @param EventFactoryLoader $anEventFactory
     */
    public function setEventFactoryLoader(EventFactoryLoader $anEventFactory)
    {
        $this->eventFactoryLoader = $anEventFactory;
    }

    /**
     * @return EventFactoryLoader
     */
    public function getEventFactoryLoader()
    {
        return $this->eventFactoryLoader;
    }

    /**
     * @param array $anInvokeStrategies
     */
    public function setInvokeStrategies(array $anInvokeStrategies)
    {
        $this->invokeStrategies = $anInvokeStrategies;
    }

    /**
     * @return array
     */
    public function getInvokeStrategies()
    {
        return $this->invokeStrategies;
    }

    /**
     * @param ServiceLocatorInterface $anInvokeStrategyLoader
     */
    public function setInvokeStrategyLoader(ServiceLocatorInterface $anInvokeStrategyLoader)
    {
        $this->invokeStrategyLoader = $anInvokeStrategyLoader;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getInvokeStrategyLoader()
    {
        if (is_null($this->invokeStrategyLoader)) {
            $this->invokeStrategyLoader = new InvokeStrategyLoader();
        }

        return $this->invokeStrategyLoader;
    }

    /**
     * @return EventManagerInterface
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->events = new EventManager(array(
                Definition::SERVICE_BUS_COMPONENT,
                'event_receiver',
                __CLASS__
            ));
        }

        return $this->events;
    }
}
 