<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 23.09.14 - 20:20
 */

namespace Prooph\ServiceBus\Router;

use Prooph\Common\Event\ActionEventDispatcher;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\EventDispatch;

/**
 * Class EventRouter
 *
 * @package Prooph\ServiceBus\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventRouter implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var array[eventName => eventListener]
     */
    protected $eventMap = array();

    /**
     * @var string
     */
    protected $tmpEventName;

    /**
     * @param null|array[eventName => eventListener] $eventMap
     */
    public function __construct(array $eventMap = null)
    {
        if (! is_null($eventMap)) {
            foreach ($eventMap as $eventName => $listeners) {

                if (is_string($listeners) || is_object($listeners) || is_callable($listeners)) {
                    $listeners = [$listeners];
                }

                $this->route($eventName);

                foreach ($listeners as $listener) {
                    $this->to($listener);
                }
            }
        }
    }

    /**
     * @param ActionEventDispatcher $events
     *
     * @return void
     */
    public function attach(ActionEventDispatcher $events)
    {
        $this->trackHandler($events->attachListener(EventDispatch::ROUTE, array($this, "onRouteEvent")));
    }

    /**
     * @param string $eventName
     * @return $this
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function route($eventName)
    {
        \Assert\that($eventName)->notEmpty()->string();

        if (! is_null($this->tmpEventName) && empty($this->eventMap[$this->tmpEventName])) {
            throw new RuntimeException(sprintf("event %s is not mapped to a listener.", $this->tmpEventName));
        }

        $this->tmpEventName = $eventName;

        if (! isset($this->eventMap[$this->tmpEventName])) {
            $this->eventMap[$this->tmpEventName] = [];
        }

        return $this;
    }

    /**
     * @param string|object|callable $eventListener
     * @return $this
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @throws \InvalidArgumentException
     */
    public function to($eventListener)
    {
        if (! is_string($eventListener) && ! is_object($eventListener) && ! is_callable($eventListener)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid event listener provided. Expected type is string, object or callable but type of %s given.",
                gettype($eventListener)
            ));
        }

        if (is_null($this->tmpEventName)) {
            throw new RuntimeException(sprintf(
                "Cannot map listener %s to an event. Please use method route before calling method to",
                (is_object($eventListener))? get_class($eventListener) : (is_string($eventListener))? $eventListener : gettype($eventListener)
            ));
        }

        $this->eventMap[$this->tmpEventName][] = $eventListener;

        return $this;
    }

    /**
     * Alias for method to
     * @param string|object|callable $eventListener
     * @return $this
     */
    public function andTo($eventListener)
    {
        return $this->to($eventListener);
    }

    /**
     * @param EventDispatch $eventDispatch
     */
    public function onRouteEvent(EventDispatch $eventDispatch)
    {
        if (is_null($eventDispatch->getEventName())) {
            $eventDispatch->getLogger()->notice(
                sprintf("%s: EventDispatch contains no event name", get_called_class())
            );
            return;
        }

        if (!isset($this->eventMap[$eventDispatch->getEventName()])) {
            if ($eventDispatch->isLoggingEnabled()) {
                $eventDispatch->getLogger()->debug(
                    sprintf(
                        "%s: Cannot route %s to a listener. No listener registered for event.",
                        get_called_class(),
                        $eventDispatch->getEventName()
                    )
                );
            }
            return;
        }

        $eventDispatch->setEventListeners($this->eventMap[$eventDispatch->getEventName()]);
    }
}
 