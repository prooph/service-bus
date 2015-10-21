<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 10/20/15 - 11:40 AM
 */

namespace Prooph\ServiceBus\Plugin;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\MessageBus;

final class StringMessageInitializerPlugin implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @param ActionEventEmitter $emitter
     */
    public function attach(ActionEventEmitter $emitter)
    {
        $emitter->attachListener(MessageBus::EVENT_INITIALIZE, [$this, 'onInitializeEvent']);
    }

    /**
     * @param ActionEvent $actionEvent
     */
    public function onInitializeEvent(ActionEvent $actionEvent)
    {
        $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);

        if (is_string($message)) {
            $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, $message);
        }
    }
}
