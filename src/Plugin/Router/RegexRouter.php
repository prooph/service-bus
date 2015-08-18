<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 30.10.14 - 22:29
 */

namespace Prooph\ServiceBus\Plugin\Router;

use Assert\Assertion;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\QueryBus;

/**
 * Class RegexRouter
 *
 * @package Prooph\ServiceBus\Router
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class RegexRouter implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    const ALL = '/.*/';

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
        if (! is_null($patternMap)) {
            foreach ($patternMap as $pattern => $handler) {
                if (is_array($handler)) {
                    foreach ($handler as $singleHandler) {
                        $this->route($pattern)->to($singleHandler);
                    }
                } else {
                    $this->route($pattern)->to($handler);
                }
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
        $this->trackHandler($events->attachListener(MessageBus::EVENT_ROUTE, [$this, 'onRoute'], 100));
    }

    /**
     * @param string $pattern
     * @return $this
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function route($pattern)
    {
        Assertion::string($pattern);
        Assertion::notEmpty($pattern);

        if (! is_null($this->tmpPattern)) {
            throw new RuntimeException(sprintf("pattern %s is not mapped to a handler.", $this->tmpPattern));
        }

        $this->tmpPattern = $pattern;

        return $this;
    }

    /**
     * @param string|object|callable $handler
     * @return $this
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @throws \InvalidArgumentException
     */
    public function to($handler)
    {
        if (! is_string($handler) && ! is_object($handler) && ! is_callable($handler)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid handler provided. Expected type is string, object or callable but type of %s given.",
                gettype($handler)
            ));
        }

        if (is_null($this->tmpPattern)) {
            throw new RuntimeException(sprintf(
                "Cannot map handler %s to a pattern. Please use method route before calling method to",
                (is_object($handler))? get_class($handler) : (is_string($handler))? $handler : gettype($handler)
            ));
        }

        $this->patternMap[] = [$this->tmpPattern => $handler];

        $this->tmpPattern = null;

        return $this;
    }

    /**
     * @param ActionEvent $actionEvent
     */
    public function onRoute(ActionEvent $actionEvent)
    {
        if ($actionEvent->getTarget() instanceof CommandBus || $actionEvent->getTarget() instanceof QueryBus) {
            $this->onRouteToSingleHandler($actionEvent);
        } else {
            $this->onRouteEvent($actionEvent);
        }
    }

    /**
     * @param ActionEvent $actionEvent
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    private function onRouteToSingleHandler(ActionEvent $actionEvent)
    {
        $messageName = (string)$actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        $alreadyMatched = false;

        foreach ($this->patternMap as $map) {
            list($pattern, $handler) = each($map);
            if (preg_match($pattern, $messageName)) {
                if ($alreadyMatched) {
                    throw new RuntimeException(sprintf(
                        "Multiple handlers detected for message %s. The patterns %s and %s matches both",
                        $messageName,
                        $alreadyMatched,
                        $pattern
                    ));
                } else {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);

                    $alreadyMatched = true;
                }
            }
        }
    }

    /**
     * @param ActionEvent $actionEvent
     */
    private function onRouteEvent(ActionEvent $actionEvent)
    {
        $messageName = (string)$actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if (empty($messageName)) {
            return;
        }

        foreach ($this->patternMap as $map) {
            list($pattern, $handler) = each($map);
            if (preg_match($pattern, $messageName)) {
                $listeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, []);
                $listeners[] = $handler;
                $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $listeners);
            }
        }
    }
}
