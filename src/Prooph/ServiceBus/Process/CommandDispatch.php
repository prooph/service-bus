<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.09.14 - 16:35
 */

namespace Prooph\ServiceBus\Process;

use Prooph\ServiceBus\CommandBus;
use Zend\EventManager\Event;
use Zend\Log\Logger;
use Zend\Log\LoggerInterface;

/**
 * Class CommandDispatch
 *
 * @package Prooph\ServiceBus\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandDispatch extends Event
{
    const INITIALIZE          = "initialize";
    const DETECT_COMMAND_NAME = "detect-command-name";
    const ROUTE               = "route";
    const LOCATE_HANDLER      = "locate-handler";
    const INVOKE_HANDLER      = "invoke-handler";
    const HANDLE_ERROR        = "handle-error";
    const FINALIZE            = "finalize";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param mixed $command
     * @param CommandBus $commandBus
     * @return CommandDispatch
     */
    public static function initializeWith($command, CommandBus $commandBus)
    {
        return new self(self::INITIALIZE, $commandBus, array('command' => $command, 'log' => new \ArrayObject()));
    }

    /**
     * @return string|null
     */
    public function getCommandName()
    {
        return $this->getParam('command-name');
    }

    /**
     * @param string $commandName
     * @return CommandDispatch
     * @throws \InvalidArgumentException
     */
    public function setCommandName($commandName)
    {
        \Assert\that($commandName)->notEmpty("Invalid command name provided.")->string("Invalid command name provided.");

        $this->setParam('command-name', $commandName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->getParam('command');
    }

    /**
     * @param mixed $command
     * @return CommandDispatch
     */
    public function setCommand($command)
    {
        $this->setParam('command', $command);
        return $this;
    }

    /**
     * @return null|string|object
     */
    public function getCommandHandler()
    {
        return $this->getParam('command-handler');
    }

    /**
     * @param string|object|callable $commandHandler
     * @return CommandDispatch
     * @throws \InvalidArgumentException
     */
    public function setCommandHandler($commandHandler)
    {
        if (! is_string($commandHandler) && ! is_object($commandHandler) && ! is_callable($commandHandler)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid command handler provided. Expected type is string, object or callable but type of %s given.",
                gettype($commandHandler)
            ));
        }

        $this->setParam("command-handler", $commandHandler);

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            $this->logger = new Logger();
            $this->logger->addWriter('null');
        }

        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function useLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
 