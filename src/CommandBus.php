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

    public function __construct(ActionEventEmitter $actionEventEmitter = null)
    {
        parent::__construct($actionEventEmitter);

        $this->events->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $commandHandler = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);

                if (is_callable($commandHandler)) {
                    $command = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                    $commandHandler($command);
                    $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, true);
                }
            },
            self::PRIORITY_INVOKE_HANDLER
        );

        $this->events->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER) === null) {
                    throw new RuntimeException(sprintf(
                        'CommandBus was not able to identify a CommandHandler for command %s',
                        $this->getMessageName($actionEvent->getParam(self::EVENT_PARAM_MESSAGE))
                    ));
                }
            },
            self::PRIORITY_LOCATE_HANDLER
        );
    }

    /**
     * @param mixed $command
     *
     * @throws CommandDispatchException
     */
    public function dispatch($command): void
    {
        $this->commandQueue[] = $command;

        if (! $this->isDispatching) {
            $this->isDispatching = true;

            $actionEventEmitter = $this->events;

            try {
                while ($command = array_shift($this->commandQueue)) {
                    $actionEvent = $actionEventEmitter->getNewActionEvent(
                        self::EVENT_DISPATCH,
                        $this,
                        [
                            self::EVENT_PARAM_MESSAGE => $command,
                        ]
                    );

                    try {
                        $actionEventEmitter->dispatch($actionEvent);

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
