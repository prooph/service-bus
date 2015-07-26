<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 20:52
 */

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;

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
     * @param mixed $handler
     * @param mixed $message
     * @return bool
     */
    abstract protected function canInvoke($handler, $message);

    /**
     * @param mixed $handler
     * @param mixed $message
     */
    abstract protected function invoke($handler, $message);

    /**
     * Attach one or more listeners
     *
     * @param ActionEventEmitter $events
     *
     * @return void
     */
    public function attach(ActionEventEmitter $events)
    {
        $this->trackHandler($events->attachListener(CommandBus::EVENT_INVOKE_HANDLER, $this, $this->priority));
        $this->trackHandler($events->attachListener(EventBus::EVENT_INVOKE_LISTENER, $this, $this->priority));
    }

    /**
     * @param ActionEvent $e
     */
    public function __invoke(ActionEvent $e)
    {
        $message = $e->getParam(MessageBus::EVENT_PARAM_MESSAGE);
        $handler = null;

        if ($e->getTarget() instanceof CommandBus) {
            $handler = $e->getParam(CommandBus::EVENT_PARAM_COMMAND_HANDLER);
        }else if ($e->getTarget() instanceof EventBus) {
            $handler = $e->getParam(EventBus::EVENT_PARAM_CURRENT_EVENT_LISTENER);
        } else {
            return;
        }

        if ($this->canInvoke($handler, $message)) {
            $this->invoke($handler, $message);
        }
    }
}
 