<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 13.01.15 - 14:59
 */

namespace Prooph\ServiceBus;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\MessageDispatch;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Psr\Log\LoggerInterface;
use Zend\Stdlib\CallbackHandler;

/**
 * Class MessageBus
 *
 * Base class for command and event bus implementations
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
abstract class MessageBus implements EventManagerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @param mixed $message
     * @return void
     */
    abstract public function dispatch($message);

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
                    "%s cannot use plugin of type %s.",
                    get_called_class(),
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
                    "%s cannot detach plugin of type %s.",
                    get_called_class(),
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
     * @param MessageDispatch $messageDispatch
     * @throws Exception\RuntimeException
     */
    protected function trigger(MessageDispatch $messageDispatch)
    {
        $result = $this->getEventManager()->trigger($messageDispatch);

        if ($result->stopped()) {
            throw new RuntimeException("Dispatch has stopped unexpectedly.");
        }
    }

    /**
     * @param MessageDispatch $messageDispatch
     */
    protected function triggerError(MessageDispatch $messageDispatch)
    {
        $messageDispatch->setName(MessageDispatch::HANDLE_ERROR);

        $this->getEventManager()->trigger($messageDispatch);
    }

    /**
     * @param MessageDispatch $messageDispatch
     */
    protected function triggerFinalize(MessageDispatch $messageDispatch)
    {
        $messageDispatch->setName(MessageDispatch::FINALIZE);

        $this->getEventManager()->trigger($messageDispatch);
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
            'message_bus',
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
 