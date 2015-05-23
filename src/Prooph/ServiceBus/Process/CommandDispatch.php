<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.09.14 - 16:35
 */

namespace Prooph\ServiceBus\Process;

use Prooph\Common\Messaging\HasMessageName;
use Prooph\Common\Messaging\MessageHeader;
use Prooph\Common\Messaging\RemoteMessage;
use Prooph\ServiceBus\CommandBus;

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
        $instance = new static(static::INITIALIZE, $commandBus, array('message' => $command));

        if ($command instanceof HasMessageName) {
            $instance->setMessageName($command->messageName());
        }

        if ($command instanceof RemoteMessage) {
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
     * @throws \InvalidArgumentException
     */
    public function setCommandName($commandName)
    {
        $this->setMessageName($commandName);
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
     */
    public function setCommand($command)
    {
        $this->setMessage($command);
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
    }
}
 