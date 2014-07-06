<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:31
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class EventReceiver
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventReceiver implements EventReceiverInterface
{
    /**
     * @var ServiceBusManager
     */
    protected $serviceBusManager;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @param ServiceBusManager $anEventHandlerLocator
     */
    public function __construct(ServiceBusManager $anEventHandlerLocator)
    {
        $this->serviceBusManager = $anEventHandlerLocator;
    }

    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return void
     */
    public function handle(MessageInterface $aMessage)
    {
        $params = array('message' => $aMessage);
        $results = $this->events()->trigger(__FUNCTION__ . '.pre', $this, $params);

        if ($results->stopped()) {
            return;
        }

        $event = $this->serviceBusManager->getEventFactoryLoader()
            ->getEventFactoryFor($aMessage->name())
            ->fromMessage($aMessage);

        $this->serviceBusManager->routeDirect($event);

        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('message' => $aMessage, 'event' => $event));
    }

    /**
     * @return EventManager
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * @param EventManagerInterface $events
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            Definition::SERVICE_BUS_COMPONENT,
            'event_receiver',
            __CLASS__
        ));

        $this->events = $events;
    }
}
 