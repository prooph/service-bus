<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 20:52
 */

namespace Prooph\ServiceBus\InvokeStrategy;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventDispatcher;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Process\EventDispatch;

/**
 * Class AbstractInvokeStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
abstract class AbstractInvokeStrategy implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    protected $priority = 0;

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    abstract protected function canInvoke($aHandler, $aCommandOrEvent);

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     */
    abstract protected function invoke($aHandler, $aCommandOrEvent);

    /**
     * Attach one or more listeners
     *
     * @param ActionEventDispatcher $events
     *
     * @return void
     */
    public function attach(ActionEventDispatcher $events)
    {
        $this->trackHandler($events->attachListener(CommandDispatch::INVOKE_HANDLER, array($this, 'onInvoke'), $this->priority));
        $this->trackHandler($events->attachListener(EventDispatch::INVOKE_LISTENER, array($this, 'onInvoke'), $this->priority));
    }

    /**
     * @param ActionEvent $e
     */
    public function onInvoke(ActionEvent $e)
    {
        $message = null;
        $handler = null;

        if ($e instanceof CommandDispatch) {
            $message = $e->getCommand();
            $handler = $e->getCommandHandler();
        }else if ($e instanceof EventDispatch) {
            $message = $e->getEvent();
            $handler = $e->getCurrentEventListener();
        } else {
            return;
        }

        if ($this->canInvoke($handler, $message)) {
            $this->invoke($handler, $message);
            if ($e->isLoggingEnabled()) {
                $e->getLogger()->info(sprintf(
                    "Message %s invoked on handler %s",
                    is_object($message)? get_class($message) : json_encode($message),
                    is_object($handler)? get_class($handler) : json_encode($handler)
                ));
            }
        }
    }
}
 