<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2019 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin\Router;

use Assert\Assertion;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\Exception;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;

class SingleHandlerRouter extends AbstractPlugin implements MessageBusRouterPlugin
{
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
        if (null === $messageMap) {
            return;
        }

        foreach ($messageMap as $messageName => $handler) {
            $this->route($messageName)->to($handler);
        }
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            [$this, 'onRouteMessage'],
            MessageBus::PRIORITY_ROUTE
        );
    }

    public function route(string $messageName): SingleHandlerRouter
    {
        Assertion::notEmpty($messageName);

        if (null !== $this->tmpMessageName) {
            throw new Exception\RuntimeException(\sprintf('Message %s is not mapped to a handler.', $this->tmpMessageName));
        }

        $this->tmpMessageName = $messageName;

        return $this;
    }

    /**
     * @param string|object|callable $messageHandler
     *
     * @return SingleHandlerRouter
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function to($messageHandler): SingleHandlerRouter
    {
        if (! \is_string($messageHandler) && ! \is_object($messageHandler) && ! \is_callable($messageHandler)) {
            throw new Exception\InvalidArgumentException(\sprintf(
                'Invalid message handler provided. Expected type is string, object or callable but type of %s given.',
                \gettype($messageHandler)
            ));
        }

        if (null === $this->tmpMessageName) {
            throw new Exception\RuntimeException(\sprintf(
                'Cannot map handler %s to a message. Please use method route before calling method to',
                \is_object($messageHandler)
                    ? \get_class($messageHandler)
                    : (\is_string($messageHandler) ? $messageHandler : \gettype($messageHandler))
            ));
        }

        $this->messageMap[$this->tmpMessageName] = $messageHandler;

        $this->tmpMessageName = null;

        return $this;
    }

    public function onRouteMessage(ActionEvent $actionEvent): void
    {
        $messageName = (string) $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        if (! isset($this->messageMap[$messageName])) {
            return;
        }

        $handler = $this->messageMap[$messageName];

        $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);
    }
}
