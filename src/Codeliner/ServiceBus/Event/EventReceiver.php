<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:31
 */

namespace Codeliner\ServiceBus\Event;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Message\MessageInterface;
use Codeliner\ServiceBus\Service\InvokeStrategyManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class EventReceiver
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException
     * @return void
     */
    public function handle(MessageInterface $aMessage)
    {
        if (!isset($this->eventMap[$aMessage->name()])) {
            return;
        }

        $event = $this->getEventFactory()->fromMessage($aMessage);

        $handler = $this->eventHandlerLocator->get($this->eventMap[$aMessage->name()]);

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
}
 