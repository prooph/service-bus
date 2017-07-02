<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;
use Psr\Container\ContainerInterface;

/**
 * This plugin can be used to lazy load message handlers.
 * Initialize it with a Psr\Container\ContainerInterface
 * and route your messages to the service id only.
 */
class ServiceLocatorPlugin extends AbstractPlugin
{
    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->listenerHandlers[] = $messageBus->attach(
            MessageBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $messageHandlerAlias = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER);

                if (is_string($messageHandlerAlias) && $this->serviceLocator->has($messageHandlerAlias)) {
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $this->serviceLocator->get($messageHandlerAlias));
                }

                // for event bus only
                $currentEventListeners = $actionEvent->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, []);
                $newEventListeners = [];

                foreach ($currentEventListeners as $key => $eventListenerAlias) {
                    if (is_string($eventListenerAlias) && $this->serviceLocator->has($eventListenerAlias)) {
                        $newEventListeners[$key] = $this->serviceLocator->get($eventListenerAlias);
                    }
                }

                // merge array whilst preserving numeric keys and giving priority to newEventListeners
                $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $newEventListeners + $currentEventListeners);
            },
            MessageBus::PRIORITY_LOCATE_HANDLER
        );
    }
}
