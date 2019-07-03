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

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;

class HandleCommandStrategy extends AbstractPlugin
{
    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED, false)) {
                    return;
                }

                $message = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE);
                $handler = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);

                $handler->handle($message);
                $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED, true);
            },
            MessageBus::PRIORITY_INVOKE_HANDLER
        );
    }
}
