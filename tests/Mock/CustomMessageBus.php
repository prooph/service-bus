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

namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\MessageBus;

class CustomMessageBus extends MessageBus
{
    private $actionEvent;

    /**
     * @param mixed $message
     */
    public function dispatch($message): void
    {
        $actionEventEmitter = $this->events;

        $actionEvent = $this->getActionEvent();
        $actionEvent->setName(self::EVENT_DISPATCH);
        $actionEvent->setTarget($this);
        $actionEvent->setParam(self::EVENT_PARAM_MESSAGE, $message);

        $actionEventEmitter->dispatch($actionEvent);
    }

    public function setActionEvent(ActionEvent $event): void
    {
        $this->actionEvent = $event;
    }

    public function getActionEvent(): ActionEvent
    {
        if (null === $this->actionEvent) {
            $this->actionEvent = $this->events->getNewActionEvent();
        }

        return $this->actionEvent;
    }
}
