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

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;
use Prooph\ServiceBus\QueryBus;

class FinderInvokeStrategy extends AbstractPlugin
{
    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $finder = $actionEvent->getParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLER);

                $query = $actionEvent->getParam(QueryBus::EVENT_PARAM_MESSAGE);

                $deferred = $actionEvent->getParam(QueryBus::EVENT_PARAM_DEFERRED);

                if (is_object($finder)) {
                    $finder->find($query, $deferred);
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED, true);
                }
            },
            QueryBus::PRIORITY_INVOKE_HANDLER
        );
    }
}
