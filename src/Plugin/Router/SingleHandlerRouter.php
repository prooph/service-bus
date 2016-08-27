<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 05/23/15 - 6:22 PM
 */

namespace Prooph\ServiceBus\Plugin\Router;

use Assert\Assertion;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\Exception;
use Prooph\ServiceBus\MessageBus;

/**
 * Class SingleHandlerRouter
 *
 * @package Prooph\ServiceBus\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SingleHandlerRouter implements MessageBusRouterPlugin, ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var array[messageName => messageHandler]
     */
    protected $messageMap = [];

    /**
     * @var string
     */
    protected $tmpMessageName;

    /**
     * @param null|array[messageName => messageHandler] $commandMap
     */
    public function __construct(array $messageMap = null)
    {
        if (null !== $messageMap) {
            foreach ($messageMap as $messageName => $handler) {
                $this->route($messageName)->to($handler);
            }
        }
    }

    /**
     * @param ActionEventEmitter $events
     *
     * @return void
     */
    public function attach(ActionEventEmitter $events)
    {
        $this->trackHandler($events->attachListener(MessageBus::EVENT_ROUTE, [$this, "onRouteMessage"]));
    }

    /**
     * @param string $messageName
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function route($messageName)
    {
        Assertion::string($messageName);
        Assertion::notEmpty($messageName);

        if (null !== $this->tmpMessageName) {
            throw new Exception\RuntimeException(sprintf("Message %s is not mapped to a handler.", $this->tmpMessageName));
        }

        $this->tmpMessageName = $messageName;

        return $this;
    }

    /**
     * @param string|object|callable $messageHandler
     * @return $this
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function to($messageHandler)
    {
        if (! is_string($messageHandler) && ! is_object($messageHandler) && ! is_callable($messageHandler)) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Invalid message handler provided. Expected type is string, object or callable but type of %s given.",
                gettype($messageHandler)
            ));
        }

        if (null === $this->tmpMessageName) {
            throw new Exception\RuntimeException(sprintf(
                "Cannot map handler %s to a message. Please use method route before calling method to",
                (is_object($messageHandler))? get_class($messageHandler) : (is_string($messageHandler))? $messageHandler : gettype($messageHandler)
            ));
        }

        $this->messageMap[$this->tmpMessageName] = $messageHandler;

        $this->tmpMessageName = null;

        return $this;
    }

    /**
     * @param ActionEvent $actionEvent
     */
    public function onRouteMessage(ActionEvent $actionEvent)
    {
        $messageName = (string)$actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        if (!isset($this->messageMap[$messageName])) {
            return;
        }

        $handler = $this->messageMap[$messageName];

        $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);
    }
}
