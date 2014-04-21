<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.04.14 - 02:37
 */

namespace Prooph\ServiceBus\EventStoreFeature;

use Prooph\ServiceBus\Service\Definition;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;

class EventStoreConnector extends AbstractListenerAggregate
{
    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('initialize', array($this, "onInitialize"));
    }

    /**
     * @param Event $e
     */
    public function onInitialize(Event $e)
    {
        /* @var $serviceBusManager \Prooph\ServiceBus\Service\ServiceBusManager */
        $serviceBusManager = $e->getTarget();

        $serviceBusManager->setService(Definition::MESSAGE_FACTORY, new EventStoreEventMessageFactory());

        $serviceBusManager->setService(Definition::EVENT_FACTORY, new EventStoreEventFactory());
    }
}
 