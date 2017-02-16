<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2013-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
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
     * @var bool
     */
    private $exposeEventMessageName;

    /**
     * @param AuthorizationService $authorizationService
     * @param bool $exposeEventMessageName
     */
    public function __construct(AuthorizationService $authorizationService, $exposeEventMessageName = false)
    {
        $this->authorizationService = $authorizationService;
        $this->exposeEventMessageName = $exposeEventMessageName;
    }

    /**
     * @param ActionEvent $actionEvent
     */
    public function onRoute(ActionEvent $actionEvent)
    {
        $messageName = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if ($this->authorizationService->isGranted(
            $messageName,
            $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE)
        )) {
            return;
        }

        $actionEvent->stopPropagation(true);

        if (! $this->exposeEventMessageName) {
            $messageName = '';
        }

        throw new UnauthorizedException($messageName);
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
