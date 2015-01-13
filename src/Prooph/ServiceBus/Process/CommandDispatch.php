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
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Message\MessageNameProvider;

/**
 * Class CommandDispatch
 *
 * @package Prooph\ServiceBus\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandDispatch extends MessageDispatch
{
    const LOCATE_HANDLER      = "locate-handler";
    const INVOKE_HANDLER      = "invoke-handler";

    /**
     * @param mixed $command
     * @param CommandBus $commandBus
     * @throws \InvalidArgumentException
     * @return CommandDispatch
     */
    public static function initializeWith($command, CommandBus $commandBus)
    {
        $instance = new self(self::INITIALIZE, $commandBus, array('message' => $command));

        if ($command instanceof MessageNameProvider) {
            $instance->setMessageName($command->getMessageName());
        }

        if ($command instanceof MessageInterface) {
            if ($command->header()->type() !== MessageHeader::TYPE_COMMAND) {
                throw new \InvalidArgumentException(
                    sprintf("Message %s cannot be handled. Message is not of type command.", $command->name())
                );
            }

            $instance->setMessageName($command->name());
        }

        return $instance;
    }

    /**
     * @return string|null
     */
    public function getCommandName()
    {
        return $this->getParam('message-name');
    }

    /**
     * @param string $commandName
     * @return CommandDispatch
     * @throws \InvalidArgumentException
     */
    public function setCommandName($commandName)
    {
        $this->setMessageName($commandName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->getMessage();
    }

    /**
     * @param mixed $command
     * @return CommandDispatch
     */
    public function setCommand($command)
    {
        $this->setMessage($command);
        return $this;
    }

    /**
     * @return null|string|object|callable
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
}
 