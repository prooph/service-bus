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

    const LOG_INFO_MSG    = "info";
    const LOG_WARNING_MSG = "warning";
    const LOG_ERROR_MSG   = "error";

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
     * @return \ArrayObject[index => array(log_type => info|warning|error, log_msg => string)]
     */
    public function getLog()
    {
        return $this->getParam('log');
    }

    /**
     * @param string $message
     * @return CommandDispatch
     * @throws \InvalidArgumentException
     */
    public function addInfoMsg($message)
    {
        \Assert\that($message)->string();
        $this->getLog()[] = array('log_type' => self::LOG_INFO_MSG, 'log_msg' => $message);

        return $this;
    }

    /**
     * @param string $message
     * @return CommandDispatch
     * @throws \InvalidArgumentException
     */
    public function addWarningMsg($message)
    {
        \Assert\that($message)->string();
        $this->getLog()[] = array('log_type' => self::LOG_WARNING_MSG, 'log_msg' => $message);

        return $this;
    }

    /**
     * @param string $message
     * @return CommandDispatch
     * @throws \InvalidArgumentException
     */
    public function addErrorMsg($message)
    {
        \Assert\that($message)->string();
        $this->getLog()[] = array('log_type' => self::LOG_ERROR_MSG, 'log_msg' => $message);
        $this->setParam('error-occurred', true);

        return $this;
    }

    /**
     * @return bool
     */
    public function isErrorOccurred()
    {
        return $this->getParam('error-occurred', false);
    }
}
 