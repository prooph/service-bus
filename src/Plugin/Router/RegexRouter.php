<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2021 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin\Router;

use Assert\Assertion;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;
use Prooph\ServiceBus\QueryBus;

class RegexRouter extends AbstractPlugin implements MessageBusRouterPlugin
{
    public const ALL = '/.*/';

    /**
     * @var array[array[pattern => handler], ...]
     */
    protected $patternMap = [];

    /**
     * @var string
     */
    protected $tmpPattern;

    /**
     * @param null|array[pattern => handler|handler[]] $patternMap
     */
    public function __construct(array $patternMap = null)
    {
        if (null === $patternMap) {
            return;
        }

        foreach ($patternMap as $pattern => $handler) {
            if (\is_array($handler)) {
                foreach ($handler as $singleHandler) {
                    $this->route($pattern)->to($singleHandler);
                }
            } else {
                $this->route($pattern)->to($handler);
            }
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

    public function route(string $pattern): RegexRouter
    {
        Assertion::notEmpty($pattern);

        if (null !== $this->tmpPattern) {
            throw new Exception\RuntimeException(\sprintf('pattern %s is not mapped to a handler.', $this->tmpPattern));
        }

        $this->tmpPattern = $pattern;

        return $this;
    }

    /**
     * @param string|object|callable $handler
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function to($handler): RegexRouter
    {
        if (! \is_string($handler) && ! \is_object($handler) && ! \is_callable($handler)) {
            throw new Exception\InvalidArgumentException(\sprintf(
                'Invalid handler provided. Expected type is string, object or callable but type of %s given.',
                \gettype($handler)
            ));
        }

        if (null === $this->tmpPattern) {
            throw new Exception\RuntimeException(\sprintf(
                'Cannot map handler %s to a pattern. Please use method route before calling method to',
                \is_object($handler)
                    ? \get_class($handler)
                    : (\is_string($handler) ? $handler : \gettype($handler))
            ));
        }

        $this->patternMap[] = [$this->tmpPattern => $handler];

        $this->tmpPattern = null;

        return $this;
    }

    public function onRouteMessage(ActionEvent $actionEvent): void
    {
        if ($actionEvent->getTarget() instanceof CommandBus || $actionEvent->getTarget() instanceof QueryBus) {
            $this->onRouteToSingleHandler($actionEvent);
        } else {
            $this->onRouteEvent($actionEvent);
        }
    }

    private function onRouteToSingleHandler(ActionEvent $actionEvent): void
    {
        $messageName = (string) $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        $alreadyMatched = false;

        foreach ($this->patternMap as $map) {
            list($pattern, $handler) = \each($map);
            if (\preg_match($pattern, $messageName)) {
                if ($alreadyMatched) {
                    throw new Exception\RuntimeException(\sprintf(
                        'Multiple handlers detected for message %s. The patterns %s and %s matches both',
                        $messageName,
                        $alreadyMatched,
                        $pattern
                    ));
                }
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);

                $alreadyMatched = true;
            }
        }
    }

    private function onRouteEvent(ActionEvent $actionEvent): void
    {
        $messageName = (string) $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        foreach ($this->patternMap as $map) {
            list($pattern, $handler) = \each($map);
            if (\preg_match($pattern, $messageName)) {
                $listeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, []);
                $listeners[] = $handler;
                $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $listeners);
            }
        }
    }
}
