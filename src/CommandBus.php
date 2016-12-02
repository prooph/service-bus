<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\ServiceBus\Exception\CommandDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;

/**
 * A command bus is capable of dispatching a message to a command handler.
 * Only one handler per message is allowed!
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

    public function setActionEventEmitter(ActionEventEmitter $actionEventEmitter): void
    {
        $actionEventEmitter->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) {
                $commandHandler = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);

                if (is_callable($commandHandler)) {
                    $command = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                    $commandHandler($command);
                    $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, true);
                }
            },
            self::PRIORITY_INVOKE_HANDLER
        );

        $actionEventEmitter->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) {
                if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER) === null) {
                    $command = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                    throw new RuntimeException(sprintf(
                        'CommandBus was not able to identify a CommandHandler for command %s',
                        $this->getMessageName($command)
                    ));
                }
            },
            self::PRIORITY_ROUTE - 1 // after routing
        );

        parent::setActionEventEmitter($actionEventEmitter);
    }

    /**
     * @param mixed $command
     *
     * @return void
     *
     * @throws CommandDispatchException
     */
    public function dispatch($command): void
    {
        $this->commandQueue[] = $command;

        if (! $this->isDispatching) {
            $this->isDispatching = true;

            try {
                while ($command = array_shift($this->commandQueue)) {
                    $actionEvent = $this->getActionEventEmitter()->getNewActionEvent(
                        self::EVENT_DISPATCH,
                        $this,
                        [
                            self::EVENT_PARAM_MESSAGE => $command,
                        ]
                    );

                    try {
                        $this->getActionEventEmitter()->dispatch($actionEvent);

                        if (! $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLED)) {
                            throw new RuntimeException(sprintf('Command %s was not handled', $this->getMessageName($command)));
                        }
                    } catch (\Throwable $exception) {
                        $actionEvent->setParam(self::EVENT_PARAM_EXCEPTION, $exception);
                    } finally {
                        $this->triggerFinalize($actionEvent);
                    }
                }
                $this->isDispatching = false;
            } catch (\Throwable $e) {
                $this->isDispatching = false;
                throw CommandDispatchException::wrap($e, $this->commandQueue);
            }
        }
    }
}
