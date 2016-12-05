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

namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\MessageBus;

class CustomMessageBus extends MessageBus
{
    public const EVENT_FOO = 'foo';

    private $actionEvent;

    /**
     * @param mixed $message
     */
    public function dispatch($message): void
    {
        $actionEventEmitter = $this->getActionEventEmitter();

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
            $this->actionEvent = $this->getActionEventEmitter()->getNewActionEvent();
        }

        return $this->actionEvent;
    }
}
