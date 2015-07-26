<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 5/23/15 - 6:22 PM
 */
namespace Prooph\ServiceBus\Router;

use Prooph\Common\Event\ActionEventDispatcher;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Process\MessageDispatch;
use Prooph\ServiceBus\Process\QueryDispatch;

/**
 * Class SingleHandlerRouter
 *
 * @package Prooph\ServiceBus\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SingleHandlerRouter implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var array[messageName => messageHandler]
     */
    protected $messageMap = array();

    /**
     * @var string
     */
    protected $tmpMessageName;

    /**
     * @param null|array[messageName => messageHandler] $commandMap
     */
    public function __construct(array $messageMap = null)
    {
        if (! is_null($messageMap)) {
            foreach ($messageMap as $messageName => $handler) {
                $this->route($messageName)->to($handler);
            }
        }
    }

    /**
     * @param ActionEventDispatcher $events
     *
     * @return void
     */
    public function attach(ActionEventDispatcher $events)
    {
        $this->trackHandler($events->attachListener(MessageDispatch::ROUTE, array($this, "onRouteMessage")));
    }

    /**
     * @param string $messageName
     * @return $this
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function route($messageName)
    {
        \Assert\that($messageName)->notEmpty()->string();

        if (! is_null($this->tmpMessageName)) {
            throw new RuntimeException(sprintf("Message %s is not mapped to a handler.", $this->tmpMessageName));
        }

        $this->tmpMessageName = $messageName;

        return $this;
    }

    /**
     * @param string|object|callable $messageHandler
     * @return $this
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @throws \InvalidArgumentException
     */
    public function to($messageHandler)
    {
        if (! is_string($messageHandler) && ! is_object($messageHandler) && ! is_callable($messageHandler)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid message handler provided. Expected type is string, object or callable but type of %s given.",
                gettype($messageHandler)
            ));
        }

        if (is_null($this->tmpMessageName)) {
            throw new RuntimeException(sprintf(
                "Cannot map handler %s to a message. Please use method route before calling method to",
                (is_object($messageHandler))? get_class($messageHandler) : (is_string($messageHandler))? $messageHandler : gettype($messageHandler)
            ));
        }

        $this->messageMap[$this->tmpMessageName] = $messageHandler;

        $this->tmpMessageName = null;

        return $this;
    }

    /**
     * @param MessageDispatch $messageDispatch
     */
    public function onRouteMessage(MessageDispatch $messageDispatch)
    {
        if (is_null($messageDispatch->getMessageName()) && $messageDispatch->isLoggingEnabled()) {
            $messageDispatch->getLogger()->notice(
                sprintf("%s: MessageDispatch contains no message name", get_called_class())
            );
            return;
        }

        if (!isset($this->messageMap[$messageDispatch->getMessageName()])) {
            if ($messageDispatch->isLoggingEnabled()) {
                $messageDispatch->getLogger()->debug(
                    sprintf(
                        "%s: Cannot route %s to a handler. No handler registered for message.",
                        get_called_class(),
                        $messageDispatch->getMessageName()
                    )
                );
            }
            return;
        }

        $handler = $this->messageMap[$messageDispatch->getMessageName()];

        if ($messageDispatch instanceof CommandDispatch) {
            $messageDispatch->setCommandHandler($handler);
        } elseif ($messageDispatch instanceof QueryDispatch) {
            $messageDispatch->setFinder($handler);
        } else {
            $messageDispatch->setParam('message-handler', $handler);
        }
    }
} 