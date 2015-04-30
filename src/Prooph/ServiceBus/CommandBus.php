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
use Zend\EventManager\EventManagerInterface;

/**
 * Class CommandBus
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandBus extends MessageBus
{
    /**
     * @param mixed $command
     * @throws Exception\CommandDispatchException
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
            $failedPhase = $commandDispatch->getName();

            $commandDispatch->setException($ex);
            $this->triggerError($commandDispatch);
            $this->triggerFinalize($commandDispatch);

            //Check if a listener has removed the exception to indicate that it was able to handle it
            if ($ex = $commandDispatch->getException()) {
                $commandDispatch->setName($failedPhase);
                throw CommandDispatchException::failed($commandDispatch, $ex);
            }

            return;
        }

        $this->triggerFinalize($commandDispatch);
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $actionEventDispatcher
     * @return void
     */
    public function setActionEventDispatcher(EventManagerInterface $actionEventDispatcher)
    {
        $actionEventDispatcher->addIdentifiers(array(
            'command_bus',
            __CLASS__
        ));

        parent::setActionEventDispatcher($actionEventDispatcher);
    }
}
 