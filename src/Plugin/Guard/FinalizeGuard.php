<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus\Plugin\Guard;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\QueryBus;
use React\Promise\Promise;

/**
 * Class FinalizeGuard
 * @package Prooph\ServiceBus\Plugin\Guard
 */
final class FinalizeGuard implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @param AuthorizationService $authorizationService
     */
    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * @param ActionEvent $actionEvent
     * @throws UnauthorizedException
     */
    public function onFinalize(ActionEvent $actionEvent)
    {
        $promise = $actionEvent->getParam(QueryBus::EVENT_PARAM_PROMISE);

        if ($promise instanceof Promise) {
            $newPromise = $promise->then(function ($result) use ($actionEvent) {
                if (!$this->authorizationService->isGranted(
                    $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME),
                    $result)
                ) {
                    $actionEvent->stopPropagation(true);

                    throw new UnauthorizedException();
                }
            });

            $actionEvent->setParam(QueryBus::EVENT_PARAM_PROMISE, $newPromise);
        } elseif (!$this->authorizationService->isGranted($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME))) {
            $actionEvent->stopPropagation(true);

            throw new UnauthorizedException();
        }
    }

    /**
     * @param ActionEventEmitter $events
     *
     * @return void
     */
    public function attach(ActionEventEmitter $events)
    {
        $this->trackHandler($events->attachListener(MessageBus::EVENT_FINALIZE, [$this, "onFinalize"], -1000));
    }
}
