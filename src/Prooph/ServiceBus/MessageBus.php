<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 13.01.15 - 14:59
 */

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEventDispatcher;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\ListenerHandler;
use Prooph\Common\Event\ProophActionEventDispatcher;
use Prooph\Common\Event\ZF2\Zf2ActionEventDispatcher;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\MessageDispatch;
use Psr\Log\LoggerInterface;

/**
 * Class MessageBus
 *
 * Base class for command and event bus implementations
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
abstract class MessageBus
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ActionEventDispatcher
     */
    protected $events;

    /**
     * @param mixed $message
     * @return mixed|void depends on the bus type
     */
    abstract public function dispatch($message);

    /**
     * @param ActionEventListenerAggregate|LoggerInterface $plugin
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function utilize($plugin)
    {
        if ($plugin instanceof ActionEventListenerAggregate) {
            $plugin->attach($this->getActionEventDispatcher());
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
     * @param ActionEventListenerAggregate|LoggerInterface $plugin
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function deactivate($plugin)
    {
        if ($plugin instanceof ActionEventListenerAggregate) {
            $plugin->detach($this->getActionEventDispatcher());
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
     * @return ListenerHandler
     */
    public function on($eventName, $listener, $priority = 1)
    {
        return $this->getActionEventDispatcher()->attachListener($eventName, $listener, $priority);
    }

    /**
     * @param ListenerHandler $listenerHandler
     * @return bool
     */
    public function off(ListenerHandler $listenerHandler)
    {
        return $this->getActionEventDispatcher()->detachListener($listenerHandler);
    }

    /**
     * @param MessageDispatch $messageDispatch
     * @throws Exception\RuntimeException
     */
    protected function trigger(MessageDispatch $messageDispatch)
    {
        $this->getActionEventDispatcher()->dispatch($messageDispatch);

        if ($messageDispatch->propagationIsStopped()) {
            throw new RuntimeException("Dispatch has stopped unexpectedly.");
        }
    }

    /**
     * @param MessageDispatch $messageDispatch
     */
    protected function triggerError(MessageDispatch $messageDispatch)
    {
        $messageDispatch->setName(MessageDispatch::HANDLE_ERROR);

        $this->getActionEventDispatcher()->dispatch($messageDispatch);
    }

    /**
     * @param MessageDispatch $messageDispatch
     */
    protected function triggerFinalize(MessageDispatch $messageDispatch)
    {
        $messageDispatch->setName(MessageDispatch::FINALIZE);

        $this->getActionEventDispatcher()->dispatch($messageDispatch);
    }


    /**
     * Inject an ActionEventDispatcher instance
     *
     * @param  ActionEventDispatcher $actionEventDispatcher
     * @return void
     */
    public function setActionEventDispatcher(ActionEventDispatcher $actionEventDispatcher)
    {
        $this->events = $actionEventDispatcher;
    }

    /**
     * Retrieve the action event dispatcher
     *
     * Lazy-loads a dispatcher if none is registered.
     *
     * @return ActionEventDispatcher
     */
    public function getActionEventDispatcher()
    {
        if (is_null($this->events)) {
            $this->setActionEventDispatcher(new ProophActionEventDispatcher());
        }

        return $this->events;
    }
}
 