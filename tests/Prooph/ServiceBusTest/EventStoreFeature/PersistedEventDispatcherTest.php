<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.04.14 - 01:10
 */
namespace Prooph\ServiceBusTest\EventStoreFeature;

use Prooph\EventSourcing\DomainEvent\AggregateChangedEvent;
use Prooph\EventSourcing\Mapping\AggregateChangedEventHydrator;
use Prooph\EventStore\Configuration\Configuration;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\EventId;
use Prooph\EventStore\Stream\EventName;
use Prooph\EventStore\Stream\StreamEvent;
use Prooph\EventStore\Stream\StreamId;
use Prooph\ServiceBus\Event\AbstractEvent;
use Prooph\ServiceBus\EventStoreFeature\EventStoreConnector;
use Prooph\ServiceBus\EventStoreFeature\PersistedEventDispatcher;
use Prooph\ServiceBus\Initializer\LocalSynchronousInitializer;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\StandardMessage;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\ServiceBusConfiguration;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\User;
use Prooph\ServiceBusTest\Mock\UserCreated;
use Prooph\ServiceBusTest\TestCase;
use ValueObjects\DateTime\DateTime;
use Zend\EventManager\Event;
use Zend\EventManager\StaticEventManager;

/**
 * Class PersistedEventDispatcherTest
 *
 * @package Prooph\ServiceBusTest\EventStoreFeature
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PersistedEventDispatcherTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatches_persisted_event_to_local_event_bus()
    {
        $userCreatedEventReceived = false;

        $serviceBusManager = new ServiceBusManager(new ServiceBusConfiguration(array(
            Definition::EVENT_MAP => array(
                'Prooph\ServiceBusTest\Mock\UserCreated' => function(AbstractEvent $e) use (&$userCreatedEventReceived) {
                    $userCreatedEventReceived = $e;
                }
            )
        )));

        StaticEventManager::getInstance();

        $serviceBusManager->initialize();

        $serviceBusManager->events()->getSharedManager()->attach('ProophMessageFactory', 'fromEvent', function(Event $e) {
            $event = $e->getParam('event');
            $sender = $e->getParam('sender');

            if ($event instanceof AggregateChangedEvent) {
                $messageHeader = new MessageHeader(
                    $event->uuid(),
                    $event->occurredOn()->toNativeDateTime(),
                    $event->version(),
                    $sender,
                    MessageHeader::TYPE_EVENT
                );

                $payload = $event->payload();

                $payload['__aggregateId__'] = $event->aggregateId();

                return new StandardMessage(get_class($event), $messageHeader, $payload);
            }
        });

        $serviceBusManager->events()->getSharedManager()->attach('ProophEventFactory', 'fromMessage', function(Event $e) {
            $message = $e->getParam('message');

            $eventClass = $message->name();

            $payload = $message->payload();

            if (! isset($payload['__aggregateId__'])) {
                return;
            }

            $aggregateId = $payload['__aggregateId__'];

            unset($payload['__aggregateId__']);

            $eventRef = new \ReflectionClass($eventClass);

            $event = $eventRef->newInstanceWithoutConstructor();

            $uuidProp = $eventRef->getProperty('uuid');

            $uuidProp->setAccessible(true);

            $uuidProp->setValue($event, $message->header()->uuid());

            $aggregateIdProp = $eventRef->getProperty('aggregateId');

            $aggregateIdProp->setAccessible(true);

            $aggregateIdProp->setValue($event, $aggregateId);

            $occurredOnProp = $eventRef->getProperty('occurredOn');

            $occurredOnProp->setAccessible(true);

            $occurredOnProp->setValue($event, DateTime::fromNativeDateTime($message->header()->createdOn()));

            $versionProp = $eventRef->getProperty('version');

            $versionProp->setAccessible(true);

            $versionProp->setValue($event, $message->header()->version());

            $payloadProp = $eventRef->getProperty('payload');

            $payloadProp->setAccessible(true);

            $payloadProp->setValue($event, $payload);

            return $event;
        });

        $config = array(
            "adapter" => array(
                'type' => "Prooph\EventStore\Adapter\Zf2\Zf2EventStoreAdapter",
                'options' => array(
                    'connection' => array(
                        'driver' => 'Pdo_Sqlite',
                        'database' => ':memory:'
                    )
                )
            ),
            "feature_manager" => array(
                "invokables" => array(
                    "ProophEventSourcingFeature" => 'Prooph\EventSourcing\EventStoreFeature\ProophEventSourcingFeature'
                ),
                "factories" => array(
                    "persisted_event_dispatcher" => function ($fm) use ($serviceBusManager) {
                        return new PersistedEventDispatcher($serviceBusManager);
                    }
                )
            ),
            "features" => array(
                "persisted_event_dispatcher",
                "ProophEventSourcingFeature"
            )
        );

        $esConfig = new Configuration($config);

        $eventStore = new EventStore($esConfig);

        $eventStore->getAdapter()->createSchema(array("User"));

        $eventStore->beginTransaction();

        $user = new User("Alex");

        $eventStore->attach($user);

        $eventStore->commit();

        $this->assertInstanceOf('Prooph\ServiceBus\Event\AbstractEvent', $userCreatedEventReceived);

        $this->assertEquals($user->id(), $userCreatedEventReceived->payload()['streamId']);
        $this->assertEquals(array('name' => 'Alex', 'streamId' => $user->id()), $userCreatedEventReceived->payload());
        $this->assertEquals(1, $userCreatedEventReceived->version());
        $this->assertInstanceOf('DateTime', $userCreatedEventReceived->occurredOn());
        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $userCreatedEventReceived->uuid());
    }
}
 