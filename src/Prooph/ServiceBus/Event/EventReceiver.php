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
use Prooph\ServiceBus\Service\InvokeStrategyManager;
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
     * @var EventFactoryInterface
     */
    protected $eventFactory;

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
    protected $invokeStrategyManager;

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

        $event = $this->getEventFactory()->fromMessage($aMessage);

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
                $invokeStrategy = $this->getInvokeStrategyManager()->get($invokeStrategyName);

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
     * @param EventFactoryInterface $anEventFactory
     */
    public function setEventFactory(EventFactoryInterface $anEventFactory)
    {
        $this->eventFactory = $anEventFactory;
    }

    /**
     * @return EventFactoryInterface
     */
    public function getEventFactory()
    {
        if (is_null($this->eventFactory)) {
            $this->eventFactory = new EventFactory();
        }

        return $this->eventFactory;
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
     * @param ServiceLocatorInterface $anInvokeStrategyManager
     */
    public function setInvokeStrategyManager(ServiceLocatorInterface $anInvokeStrategyManager)
    {
        $this->invokeStrategyManager = $anInvokeStrategyManager;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getInvokeStrategyManager()
    {
        if (is_null($this->invokeStrategyManager)) {
            $this->invokeStrategyManager = new InvokeStrategyManager();
        }

        return $this->invokeStrategyManager;
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
 