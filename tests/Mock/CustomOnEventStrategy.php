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

namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;

final class CustomOnEventStrategy extends AbstractPlugin
{
    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $target = $actionEvent->getTarget();
                $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
                $handlers = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, []);

                foreach ($handlers as $handler) {
                    if (\is_callable($handler) || ! \is_object($handler) || ! \is_callable([$handler, 'on'])) {
                        continue;
                    }

                    try {
                        $handler->on($message);
                    } catch (\Throwable $e) {
                        if ($target->isCollectingExceptions()) {
                            $target->addCollectedException($e);
                        } else {
                            throw $e;
                        }
                    }
                }

                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED, true);
            },
            MessageBus::PRIORITY_INVOKE_HANDLER
        );
    }
}
