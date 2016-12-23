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

namespace Prooph\ServiceBus\Plugin\Guard;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;
use Prooph\ServiceBus\QueryBus;
use React\Promise\Promise;

final class FinalizeGuard extends AbstractPlugin
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
            MessageBus::EVENT_FINALIZE,
            function (ActionEvent $actionEvent): void {
                $promise = $actionEvent->getParam(QueryBus::EVENT_PARAM_PROMISE);
                $messageName = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

                if ($promise instanceof Promise) {
                    $newPromise = $promise->then(function ($result) use ($actionEvent, $messageName): void {
                        if (! $this->authorizationService->isGranted($messageName, $result)) {
                            $actionEvent->stopPropagation(true);

                            if (! $this->exposeEventMessageName) {
                                $messageName = '';
                            }

                            throw new UnauthorizedException($messageName);
                        }
                    });

                    $actionEvent->setParam(QueryBus::EVENT_PARAM_PROMISE, $newPromise);
                } elseif (! $this->authorizationService->isGranted($messageName)) {
                    $actionEvent->stopPropagation(true);

                    if (! $this->exposeEventMessageName) {
                        $messageName = '';
                    }

                    throw new UnauthorizedException($messageName);
                }
            },
            -1000
        );
    }
}
