<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus\Plugin\Router;

use Assert\Assertion;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception;
use Prooph\ServiceBus\MessageBus;

/**
 * Class EventRouter
 *
 * @package Prooph\ServiceBus\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventRouter implements MessageBusRouterPlugin, ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var array[eventName => eventListener]
     */
    protected $eventMap = [];

    /**
     * @var string
     */
    protected $tmpEventName;

    /**
     * @param null|array[eventName => eventListener] $eventMap
     */
    public function __construct(array $eventMap = null)
    {
        if (null === $eventMap) {
            return;
        }

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

    public function attach(ActionEventEmitter $events): void
    {
        $this->trackHandler($events->attachListener(MessageBus::EVENT_ROUTE, [$this, "onRouteMessage"]));
    }

    public function route(string $eventName): EventRouter
    {
        Assertion::notEmpty($eventName);

        if (null !== $this->tmpEventName && empty($this->eventMap[$this->tmpEventName])) {
            throw new Exception\RuntimeException(sprintf("event %s is not mapped to a listener.", $this->tmpEventName));
        }

        $this->tmpEventName = $eventName;

        if (! isset($this->eventMap[$this->tmpEventName])) {
            $this->eventMap[$this->tmpEventName] = [];
        }

        return $this;
    }

    /**
     * @param string|object|callable $eventListener
     *
     * @return EventRouter
     *
     * @throws Exception\RuntimeException
     *
     * @throws Exception\InvalidArgumentException
     */
    public function to($eventListener): EventRouter
    {
        if (! is_string($eventListener) && ! is_object($eventListener) && ! is_callable($eventListener)) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Invalid event listener provided. Expected type is string, object or callable but type of %s given.",
                gettype($eventListener)
            ));
        }

        if (null === $this->tmpEventName) {
            throw new Exception\RuntimeException(sprintf(
                "Cannot map listener %s to an event. Please use method route before calling method to",
                (is_object($eventListener))
                    ? get_class($eventListener)
                    : (is_string($eventListener))? $eventListener : gettype($eventListener)
            ));
        }

        $this->eventMap[$this->tmpEventName][] = $eventListener;

        return $this;
    }

    /**
     * Alias for method to
     *
     * @param string|object|callable $eventListener
     *
     * @return EventRouter
     */
    public function andTo($eventListener): EventRouter
    {
        return $this->to($eventListener);
    }

    public function onRouteMessage(ActionEvent $actionEvent): void
    {
        $messageName = (string)$actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        if (! isset($this->eventMap[$messageName])) {
            return;
        }

        $listeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, []);

        $listeners = array_merge($listeners, $this->eventMap[$messageName]);

        $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $listeners);
    }
}
