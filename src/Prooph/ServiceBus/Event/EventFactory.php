<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:03
 */

namespace Prooph\ServiceBus\Event;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageInterface;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

/**
 * Class EventFactory
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventFactory implements EventFactoryInterface
{
    /**
     * @var EventManager
     */
    protected $lifeCycleEvents;

    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return mixed an Event
     */
    public function fromMessage(MessageInterface $aMessage)
    {
        $result = $this->getLifeCycleEvents()->triggerUntil(
            __FUNCTION__,
            $this,
            array('message' => $aMessage),
            function ($res) {
                return !empty($res);
            }
        );

        if (! $result->stopped()) {
            throw new RuntimeException(
                sprintf(
                    "Event %s can not be build from Message. No appropriate EventFactory registered",
                    $aMessage->name()
                )

            );
        }

        return $result->last();
    }

    public function setEventManager(EventManager $eventManager)
    {
        $eventManager->addIdentifiers(array(
            'ProophEventFactory',
            get_class($this)
        ));

        $eventManager->attach('fromMessage', function(Event $e) {

            $message = $e->getParam('message');

            $eventClass = $message->name();

            if (!class_exists($eventClass)) {
                throw new RuntimeException(
                    sprintf(
                        "Class for %s event can not be found",
                        $eventClass
                    )
                );
            }

            $eventRef = new \ReflectionClass($eventClass);

            if ($eventClass !== 'Prooph\ServiceBus\Event\AbstractEvent'
                && ! $eventRef->isSubclassOf('Prooph\ServiceBus\Event\AbstractEvent')) {
                return null;
            }

            return new $eventClass(
                $eventClass,
                $message->payload(),
                $message->header()->version(),
                $message->header()->uuid(),
                $message->header()->createdOn()
            );
        }, -100);

        $this->lifeCycleEvents = $eventManager;
    }

    /**
     * @return EventManager
     */
    public function getLifeCycleEvents()
    {
        if (is_null($this->lifeCycleEvents)) {
            $this->setEventManager(new EventManager());
        }

        return $this->lifeCycleEvents;
    }
}
