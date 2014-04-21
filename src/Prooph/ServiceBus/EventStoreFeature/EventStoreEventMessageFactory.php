<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.04.14 - 02:11
 */

namespace Prooph\ServiceBus\EventStoreFeature;

use Prooph\EventStore\EventSourcing\AggregateChangedEvent;
use Prooph\ServiceBus\Event\EventInterface;
use Prooph\ServiceBus\Message\MessageFactory;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\StandardMessage;
use Zend\Serializer\Serializer;
use Zend\Stdlib\MessageInterface;

/**
 * Class EventStoreEventMessageFactory
 *
 * @package Prooph\ServiceBus\EventStoreFeature
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventStoreEventMessageFactory extends MessageFactory
{
    /**
     * @param EventInterface $anEvent
     * @param string         $aSenderName
     * @return MessageInterface
     */
    public function fromEvent(EventInterface $anEvent, $aSenderName)
    {
        $messageHeader = new MessageHeader(
            $anEvent->uuid(),
            $anEvent->occurredOn(),
            $anEvent->version(),
            $aSenderName,
            MessageHeader::TYPE_EVENT
        );

        $payload = $anEvent->payload();

        if ($anEvent instanceof AggregateChangedEvent) {
            $payload['__aggregateId'] = Serializer::serialize($anEvent->aggregateId());
        }

        return new StandardMessage(get_class($anEvent), $messageHeader, $payload);
    }
}
 