<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.10.14 - 22:29
 */

namespace Prooph\ServiceBus\Router;

use Assert\Assertion;
use Prooph\Common\Event\ActionEventDispatcher;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Process\EventDispatch;
use Prooph\ServiceBus\Process\MessageDispatch;

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
    protected $patternMap = array();

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
                    foreach($handler as $singleHandler) $this->route($pattern)->to($singleHandler);
                } else {
                    $this->route($pattern)->to($handler);
                }

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
        $this->trackHandler($events->attachListener(MessageDispatch::ROUTE, [$this, 'onRoute'], 100));
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
     * @param MessageDispatch $messageDispatch
     */
    public function onRoute(MessageDispatch $messageDispatch)
    {
        if ($messageDispatch instanceof CommandDispatch) $this->onRouteCommand($messageDispatch);
        else $this->onRouteEvent($messageDispatch);
    }

    /**
     * @param CommandDispatch $commandDispatch
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    private function onRouteCommand(CommandDispatch $commandDispatch)
    {
        if (is_null($commandDispatch->getCommandName())) {
            $commandDispatch->getLogger()->notice(
                sprintf("%s: CommandDispatch contains no command name", get_called_class())
            );
            return;
        }

        $alreadyMatched = false;

        foreach($this->patternMap as $map) {
            list($pattern, $handler) = each($map);
            if (preg_match($pattern, $commandDispatch->getCommandName())) {

                if ($alreadyMatched) {
                    throw new RuntimeException(sprintf(
                        "Multiple handlers detected for command %s. The patterns %s and %s matches both",
                        $commandDispatch->getCommandName(),
                        $alreadyMatched,
                        $pattern
                    ));
                } else {
                    $commandDispatch->setCommandHandler($handler);
                    $alreadyMatched = $pattern;
                }
            }
        }
    }

    /**
     * @param EventDispatch $eventDispatch
     */
    private function onRouteEvent(EventDispatch $eventDispatch)
    {
        if (is_null($eventDispatch->getEventName())) {
            $eventDispatch->getLogger()->notice(
                sprintf("%s: EventDispatch contains no event name", get_called_class())
            );
            return;
        }

        foreach($this->patternMap as $map) {
            list($pattern, $handler) = each($map);
            if (preg_match($pattern, $eventDispatch->getEventName())) {
                $eventDispatch->addEventListener($handler);
            }
        }
    }
}
 