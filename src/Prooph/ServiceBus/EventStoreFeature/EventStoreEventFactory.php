<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.04.14 - 02:15
 */

namespace Prooph\ServiceBus\EventStoreFeature;

use Prooph\ServiceBus\Event\EventFactory;
use Prooph\ServiceBus\Event\EventFactoryInterface;
use Prooph\ServiceBus\Event\EventInterface;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageInterface;
use Zend\Serializer\Serializer;

/**
 * Class EventStoreEventFactory
 *
 * @package Prooph\ServiceBus\EventStoreFeature
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventStoreEventFactory implements EventFactoryInterface
{
    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return EventInterface
     */
    public function fromMessage(MessageInterface $aMessage)
    {
        $eventClass = $aMessage->name();

        if (!class_exists($eventClass)) {
            throw new RuntimeException(
                sprintf(
                    "Class for %s event can not be found",
                    $eventClass
                )
            );
        }

        $payload = $aMessage->payload();

        if (isset($payload['__aggregateId'])) {
            $eventRef = new \ReflectionClass($eventClass);

            $event = $eventRef->newInstanceWithoutConstructor();

            $this->setProperty($eventRef, $event, "uuid", $aMessage->header()->uuid());

            $this->setProperty($eventRef, $event, "version", $aMessage->header()->version());

            $this->setProperty($eventRef, $event, "occurredOn", $aMessage->header()->createdOn());

            $aggregateId = Serializer::unserialize($payload['__aggregateId']);

            unset($payload['__aggregateId']);

            $this->setProperty($eventRef, $event, "aggregateId", $aggregateId);

            $this->setProperty($eventRef, $event, "payload", $payload);

            return $event;
        } else {
            return new $eventClass(
                $aMessage->payload(),
                $aMessage->header()->version(),
                $aMessage->header()->uuid(),
                $aMessage->header()->createdOn()
            );
        }
    }

    /**
     * @param \ReflectionClass $ref
     * @param mixed $instance
     * @param string $propertyName
     * @param mixed $value
     */
    protected function setProperty(\ReflectionClass $ref, $instance, $propertyName, $value)
    {
        $prop = $ref->getProperty($propertyName);

        $prop->setAccessible(true);

        $prop->setValue($instance, $value);
    }
}
 