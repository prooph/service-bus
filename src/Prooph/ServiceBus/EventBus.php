<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 21:33
 */

namespace Prooph\ServiceBus;

use Prooph\ServiceBus\Exception\EventDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\EventDispatch;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\LoggerInterface;
use Zend\Stdlib\CallbackHandler;

class EventBus
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventManager
     */
    protected $events;

    /**
     * @param ListenerAggregateInterface|LoggerInterface $plugin
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function utilize($plugin)
    {
        if ($plugin instanceof ListenerAggregateInterface) {
            $plugin->attach($this->getEventManager());
        } else if ($plugin instanceof LoggerInterface) {
            $this->logger = $plugin;
        } else {
            throw new RuntimeException(
                sprintf(
                    "EventBus cannot use plugin of type %s.",
                    (is_object($plugin))? get_class($plugin) : gettype($plugin)
                )
            );
        }

        return $this;
    }

    /**
     * @param ListenerAggregateInterface|LoggerInterface $plugin
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function deactivate($plugin)
    {
        if ($plugin instanceof ListenerAggregateInterface) {
            $plugin->detach($this->getEventManager());
        } else if ($plugin instanceof LoggerInterface) {
            $this->logger = null;
        } else {
            throw new RuntimeException(
                sprintf(
                    "EventBus cannot detach plugin of type %s.",
                    (is_object($plugin))? get_class($plugin) : gettype($plugin)
                )
            );
        }

        return $this;
    }

    /**
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     * @return \Zend\Stdlib\CallbackHandler
     */
    public function on($eventName, $listener, $priority = 1)
    {
        return $this->getEventManager()->attach($eventName, $listener, $priority);
    }

    /**
     * @param CallbackHandler $callbackHandler
     * @return bool
     */
    public function off(CallbackHandler $callbackHandler)
    {
        return $this->getEventManager()->detach($callbackHandler);
    }

    /**
     * @param mixed $event
     * @throws Exception\CommandDispatchException
     * @return bool
     */
    public function dispatch($event)
    {
        $eventDispatch = EventDispatch::initializeWith($event, $this);

        if (! is_null($this->logger)) {
            $eventDispatch->useLogger($this->logger);
        }

        try {
            $this->trigger($eventDispatch);

            if (is_null($eventDispatch->getEventName())) {
                $eventDispatch->setName(EventDispatch::DETECT_MESSAGE_NAME);

                $this->trigger($eventDispatch);
            }

            $eventDispatch->setName(EventDispatch::ROUTE);

            $this->trigger($eventDispatch);

            foreach ($eventDispatch->getEventListeners() as $eventListener) {

                $eventDispatch->setCurrentEventListener($eventListener);

                if (is_string($eventListener)) {
                    $eventDispatch->setName(EventDispatch::LOCATE_LISTENER);

                    $this->trigger($eventDispatch);
                }

                $eventDispatch->setName(EventDispatch::INVOKE_LISTENER);

                $this->trigger($eventDispatch);
            }

        } catch (\Exception $ex) {
            $eventDispatch->setException($ex);
            $this->triggerError($eventDispatch);
            $this->triggerFinalize($eventDispatch);
            throw EventDispatchException::failed($eventDispatch, $ex);
        }

        $this->triggerFinalize($eventDispatch);
    }

    /**
     * @param EventDispatch $eventDispatch
     * @throws Exception\RuntimeException
     */
    protected function trigger(EventDispatch $eventDispatch)
    {
        $result = $this->getEventManager()->trigger($eventDispatch);

        if ($result->stopped()) {
            throw new RuntimeException("Dispatch has stopped unexpectedly.");
        }
    }

    /**
     * @param EventDispatch $eventDispatch
     */
    protected function triggerError(EventDispatch $eventDispatch)
    {
        $eventDispatch->setName(EventDispatch::HANDLE_ERROR);

        $this->getEventManager()->trigger($eventDispatch);
    }

    /**
     * @param Process\EventDispatch $eventDispatch
     */
    protected function triggerFinalize(EventDispatch $eventDispatch)
    {
        $eventDispatch->setName(EventDispatch::FINALIZE);

        $this->getEventManager()->trigger($eventDispatch);
    }


    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers(array(
            'event_bus',
            __CLASS__
        ));

        $this->events = $eventManager;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (is_null($this->events)) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }
}
 