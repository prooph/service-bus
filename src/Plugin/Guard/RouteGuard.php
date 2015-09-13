<?php

namespace Prooph\ServiceBus\Plugin\Guard;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\MessageBus;

/**
 * Class RouteGuard
 * @package Prooph\ServiceBus\Plugin\Guard
 */
final class RouteGuard implements ActionEventListenerAggregate
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
     */
    public function onRoute(ActionEvent $actionEvent)
    {
        if ($this->authorizationService->isGranted($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME))) {
            return;
        }

        $actionEvent->stopPropagation(true);

        throw new UnauthorizedException();
    }

    /**
     * @param ActionEventEmitter $events
     *
     * @return void
     */
    public function attach(ActionEventEmitter $events)
    {
        $this->trackHandler($events->attachListener(MessageBus::EVENT_ROUTE, [$this, "onRoute"], 1000));
    }
}
