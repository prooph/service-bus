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
use Zend\EventManager\Event as ProcessEvent;
use Zend\Log\Logger;
use Zend\Log\LoggerInterface;

/**
 * Class CommandDispatch
 *
 * @package Prooph\ServiceBus\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandDispatch extends ProcessEvent
{
    const INITIALIZE          = "initialize";
    const DETECT_MESSAGE_NAME = "detect-message-name";
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
     * @throws \InvalidArgumentException
     * @return CommandDispatch
     */
    public static function initializeWith($command, CommandBus $commandBus)
    {
        $instance = new self(self::INITIALIZE, $commandBus, array('command' => $command));

        if ($command instanceof MessageNameProvider) {
            $instance->setCommandName($command->getMessageName());
        }

        if ($command instanceof MessageInterface) {
            if ($command->header()->type() !== MessageHeader::TYPE_COMMAND) {
                throw new \InvalidArgumentException(
                    sprintf("Message %s cannot be handled. Message is not of type command.", $command->name())
                );
            }

            $instance->setCommandName($command->name());
        }

        return $instance;
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
 