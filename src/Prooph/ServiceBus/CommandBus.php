<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.09.14 - 16:32
 */

namespace Prooph\ServiceBus;

use Prooph\ServiceBus\Exception\CommandDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\CommandDispatch;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Log\LoggerInterface;
use Zend\Stdlib\CallbackHandler;

/**
 * Class CommandBus
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandBus implements EventManagerAwareInterface
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
                    "CommandBus cannot use plugin of type %s.",
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
                    "CommandBus cannot detach plugin of type %s.",
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
     * @param mixed $command
     * @throws Exception\CommandDispatchException
     * @return bool
     */
    public function dispatch($command)
    {
        $commandDispatch = CommandDispatch::initializeWith($command, $this);

        if (! is_null($this->logger)) {
            $commandDispatch->useLogger($this->logger);
        }

        try {
            $this->trigger($commandDispatch);

            if (is_null($commandDispatch->getCommandName())) {
                $commandDispatch->setName(CommandDispatch::DETECT_MESSAGE_NAME);

                $this->trigger($commandDispatch);
            }

            if (is_null($commandDispatch->getCommandHandler())) {
                $commandDispatch->setName(CommandDispatch::ROUTE);

                $this->trigger($commandDispatch);
            }

            if (is_null($commandDispatch->getCommandHandler())) {
                throw new RuntimeException(sprintf(
                    "CommandBus was not able to identify a CommandHandler for command %s",
                    (is_object($command))? get_class($command) : json_encode($command)
                ));
            }

            if (is_string($commandDispatch->getCommandHandler())) {
                $commandDispatch->setName(CommandDispatch::LOCATE_HANDLER);

                $this->trigger($commandDispatch);
            }

            $commandDispatch->setName(CommandDispatch::INVOKE_HANDLER);

            $this->trigger($commandDispatch);

        } catch (\Exception $ex) {
            $this->triggerError($commandDispatch);
            $this->triggerFinalize($commandDispatch);
            throw CommandDispatchException::failed($commandDispatch, $ex);
        }

        $this->triggerFinalize($commandDispatch);
    }

    /**
     * @param CommandDispatch $commandDispatch
     * @throws Exception\RuntimeException
     */
    protected function trigger(CommandDispatch $commandDispatch)
    {
        $result = $this->getEventManager()->trigger($commandDispatch);

        if ($result->stopped()) {
            throw new RuntimeException("Dispatch has stopped unexpectedly.");
        }
    }

    /**
     * @param CommandDispatch $commandDispatch
     */
    protected function triggerError(CommandDispatch $commandDispatch)
    {
        $commandDispatch->setName(CommandDispatch::HANDLE_ERROR);

        $this->getEventManager()->trigger($commandDispatch);
    }

    /**
     * @param CommandDispatch $commandDispatch
     */
    protected function triggerFinalize(CommandDispatch $commandDispatch)
    {
        $commandDispatch->setName(CommandDispatch::FINALIZE);

        $this->getEventManager()->trigger($commandDispatch);
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
            'command_bus',
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
 