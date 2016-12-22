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

namespace Prooph\ServiceBus\Plugin\Router;

use Interop\Container\ContainerInterface;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;

final class ServiceLocatorEventRouter extends AbstractPlugin implements MessageBusRouterPlugin
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            [$this, 'onRouteMessage'],
            MessageBus::PRIORITY_ROUTE
        );
    }

    public function onRouteMessage(ActionEvent $actionEvent): void
    {
        $messageName = (string) $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

        if ($this->container->has($messageName)) {
            $actionEvent->setParam(
                EventBus::EVENT_PARAM_EVENT_LISTENERS,
                $this->container->get($messageName)
            );
        }
    }
}
