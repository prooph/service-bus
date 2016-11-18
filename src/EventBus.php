<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;

/**
 * An event bus is capable of dispatching a message to multiple listeners.
 */
class EventBus extends MessageBus
{
    public const EVENT_PARAM_EVENT_LISTENERS = 'event-listeners';

    public function setActionEventEmitter(ActionEventEmitter $actionEventDispatcher): void
    {
        $actionEventDispatcher->attachListener(self::EVENT_INVOKE_HANDLER, function (ActionEvent $actionEvent) {
            $eventListener = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);
            if (is_callable($eventListener)) {
                $event = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                $eventListener($event);
                $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, true);
            }
        });

        $this->events = $actionEventDispatcher;
    }

    /**
     * @param mixed $event
     *
     * @return void
     */
    public function dispatch($event): void
    {
        $actionEvent = $this->getActionEventEmitter()->getNewActionEvent();

        $actionEvent->setTarget($this);

        try {
            $this->initialize($event, $actionEvent);

            $actionEvent->setName(self::EVENT_ROUTE);

            $this->trigger($actionEvent);

            foreach ($actionEvent->getParam(self::EVENT_PARAM_EVENT_LISTENERS, []) as $eventListener) {
                $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLER, $eventListener);

                if (is_string($eventListener) && ! is_callable($eventListener)) {
                    $actionEvent->setName(self::EVENT_LOCATE_HANDLER);
                    $this->trigger($actionEvent);
                }

                $actionEvent->setName(self::EVENT_INVOKE_HANDLER);
                $this->trigger($actionEvent);
            }

            $this->triggerFinalize($actionEvent);
        } catch (\Throwable $ex) {
            $this->handleException($actionEvent, $ex);
        }
    }
}
