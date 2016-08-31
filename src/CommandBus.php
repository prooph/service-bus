<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\ServiceBus\Exception\CommandDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;

/**
 * Class CommandBus
 *
 * A command bus is capable of dispatching a message to a command handler.
 * Only one handler per message is allowed!
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandBus extends MessageBus
{
    /**
     * @var array
     */
    private $commandQueue = [];

    /**
     * @var bool
     */
    private $isDispatching = false;

    /**
     * Inject an ActionEventDispatcher instance
     *
     * @param  ActionEventEmitter $actionEventDispatcher
     * @return void
     */
    public function setActionEventEmitter(ActionEventEmitter $actionEventDispatcher)
    {
        $actionEventDispatcher->attachListener(self::EVENT_INVOKE_HANDLER, function (ActionEvent $actionEvent) {
            $commandHandler = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);

            if (is_callable($commandHandler)) {
                $command        = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                $commandHandler($command);
                $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, true);
            }
        });

        $this->events = $actionEventDispatcher;
    }

    /**
     * @param mixed $command
     * @throws CommandDispatchException
     * @return void
     */
    public function dispatch($command)
    {
        $this->commandQueue[] = $command;

        if (! $this->isDispatching) {
            $this->isDispatching = true;

            try {
                while ($command = array_shift($this->commandQueue)) {
                    $this->processCommand($command);
                }
                $this->isDispatching = false;
            } catch (\Exception $e) {
                $this->isDispatching = false;
                throw CommandDispatchException::wrap($e, $this->commandQueue);
            }
        }
    }

    protected function processCommand($command)
    {
        $actionEvent = $this->getActionEventEmitter()->getNewActionEvent();

        $actionEvent->setTarget($this);

        try {
            $this->initialize($command, $actionEvent);

            if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER) === null) {
                $actionEvent->setName(self::EVENT_ROUTE);

                $this->trigger($actionEvent);
            }

            if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER) === null) {
                throw new RuntimeException(sprintf(
                    "CommandBus was not able to identify a CommandHandler for command %s",
                    $this->getMessageName($command)
                ));
            }

            $handler = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);

            if (is_string($handler) && ! is_callable($handler)) {
                $actionEvent->setName(self::EVENT_LOCATE_HANDLER);

                $this->trigger($actionEvent);
            }

            $actionEvent->setName(self::EVENT_INVOKE_HANDLER);
            $this->trigger($actionEvent);

            if (! $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLED)) {
                throw new RuntimeException(sprintf('Command %s was not handled', $this->getMessageName($command)));
            }

            $this->triggerFinalize($actionEvent);
        } catch (\Exception $ex) {
            $this->handleException($actionEvent, $ex);
        }
    }
}
