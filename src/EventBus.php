<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 21:33
 */

namespace Prooph\ServiceBus;

/**
 * Class EventBus
 *
 * An event bus is capable of dispatching a message to multiple listeners.
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventBus extends MessageBus
{
    const EVENT_PARAM_EVENT_LISTENERS = 'event-listeners';

    /**
     * @param mixed $event
     * @return void
     */
    public function dispatch($event)
    {
        $actionEvent = $this->getActionEventEmitter()->getNewActionEvent();

        $actionEvent->setTarget($this);

        try {
            $this->initialize($event, $actionEvent);

            $actionEvent->setName(self::EVENT_ROUTE);

            $this->trigger($actionEvent);

            foreach ($actionEvent->getParam(self::EVENT_PARAM_EVENT_LISTENERS, []) as $eventListener) {

                $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLER, $eventListener);

                if (is_string($eventListener)) {
                    $actionEvent->setName(self::EVENT_LOCATE_HANDLER);

                    $this->trigger($actionEvent);
                }

                $eventListener = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);

                if (is_callable($eventListener)) {
                    $eventListener($event);
                } else {
                    $actionEvent->setName(self::EVENT_INVOKE_HANDLER);

                    $this->trigger($actionEvent);
                }
            }

            $this->triggerFinalize($actionEvent);
        } catch (\Exception $ex) {
            $this->handleException($actionEvent, $ex);
        }
    }
}
 