<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.09.14 - 23:05
 */

namespace Prooph\ServiceBus\Router;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\CommandDispatch;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Class CommandRouter
 *
 * @package Prooph\ServiceBus\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandRouter extends AbstractListenerAggregate
{
    /**
     * @var array[commandName => commandHandler]
     */
    protected $commandMap = array();

    /**
     * @var string
     */
    protected $tmpCommandName;

    /**
     * @param null|array[commandName => commandHandler] $commandMap
     */
    public function __construct(array $commandMap = null)
    {
        if (! is_null($commandMap)) {
            foreach ($commandMap as $commandName => $handler) {
                $this->route($commandName)->to($handler);
            }
        }
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(CommandDispatch::ROUTE, array($this, "onRouteEvent"));
    }

    /**
     * @param string $commandName
     * @return $this
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function route($commandName)
    {
        \Assert\that($commandName)->notEmpty()->string();

        if (! is_null($this->tmpCommandName)) {
            throw new RuntimeException(sprintf("Command %s is not mapped to a handler.", $this->tmpCommandName));
        }

        $this->tmpCommandName = $commandName;

        return $this;
    }

    /**
     * @param string|object|callable $commandHandler
     * @return $this
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @throws \InvalidArgumentException
     */
    public function to($commandHandler)
    {
        if (! is_string($commandHandler) && ! is_object($commandHandler) && ! is_callable($commandHandler)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid command handler provided. Expected type is string, object or callable but type of %s given.",
                gettype($commandHandler)
            ));
        }

        if (is_null($this->tmpCommandName)) {
            throw new RuntimeException(sprintf(
                "Cannot map handler %s to a command. Please use method route before calling method to",
                (is_object($commandHandler))? get_class($commandHandler) : (is_string($commandHandler))? $commandHandler : gettype($commandHandler)
            ));
        }

        $this->commandMap[$this->tmpCommandName] = $commandHandler;

        $this->tmpCommandName = null;

        return $this;
    }

    /**
     * @param CommandDispatch $commandDispatch
     */
    public function onRouteEvent(CommandDispatch $commandDispatch)
    {
        if (is_null($commandDispatch->getCommandName())) {
            $commandDispatch->getLogger()->notice(
                sprintf("%s: CommandDispatch contains no command name", get_called_class())
            );
            return;
        }

        if (!isset($this->commandMap[$commandDispatch->getCommandName()])) {
            $commandDispatch->getLogger()->debug(
                sprintf(
                    "%s: Cannot route %s to a handler. No handler registered for command.",
                    get_called_class(),
                    $commandDispatch->getCommandName()
                )
            );
            return;
        }

        $commandDispatch->setCommandHandler($this->commandMap[$commandDispatch->getCommandName()]);
    }
}
 