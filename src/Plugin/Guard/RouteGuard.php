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

namespace Prooph\ServiceBus\Plugin\Guard;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;

final class RouteGuard extends AbstractPlugin
{
    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var bool
     */
    private $exposeEventMessageName;

    public function __construct(AuthorizationService $authorizationService, bool $exposeEventMessageName = false)
    {
        $this->authorizationService = $authorizationService;
        $this->exposeEventMessageName = $exposeEventMessageName;
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
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
            },
            MessageBus::PRIORITY_ROUTE
        );
    }
}
