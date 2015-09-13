<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 09/13/15 - 15:30
 */

namespace Prooph\ServiceBus\Plugin\Guard;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\ServiceBus\MessageBus;
use React\Promise\Deferred;

/**
 * Class FinalizeGuard
 * @package Prooph\ServiceBus\Plugin\Guard
 */
final class FinalizeGuard implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    const EVENT_PARAM_DEFERRED = 'query-deferred';

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
    public function onFinalize(ActionEvent $actionEvent)
    {
        $deferred = $actionEvent->getParam(self::EVENT_PARAM_DEFERRED);

        if ($deferred instanceof Deferred) {
            $deferred->promise()->done(function ($result) use ($actionEvent, $deferred) {
                if (!$this->authorizationService->isGranted(
                    $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME),
                    $result)
                ) {
                    $actionEvent->stopPropagation(true);

                    throw new UnauthorizedException();
                }
            });
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
