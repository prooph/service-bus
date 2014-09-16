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

use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Configuration\Configuration;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\AggregateStreamStrategy;
use Prooph\EventStore\Stream\StreamId;
use Prooph\ServiceBus\Event\AbstractEvent;
use Prooph\ServiceBus\EventStoreFeature\EventStoreConnector;
use Prooph\ServiceBus\EventStoreFeature\PersistedEventDispatcher;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\StandardMessage;
use Prooph\ServiceBusTest\Mock\User;
use Prooph\ServiceBusTest\Mock\UserCreated;
use Prooph\ServiceBusTest\TestCase;
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
        $this->markTestSkipped("Reactivate this when refactoring is done");
        $userCreatedEventReceived = false;

        $serviceBusManager = new ServiceBusManager(new ServiceBusConfiguration(array(
            Definition::EVENT_MAP => array(
                'Prooph\EventSourcingTest\Mock\UserCreated' => function(AbstractEvent $e) use (&$userCreatedEventReceived) {
                    $userCreatedEventReceived = $e;
                }
            )
        )));

        StaticEventManager::getInstance();

        $serviceBusManager->initialize();

        $serviceBusManager->events()->getSharedManager()->attach('ProophMessageFactory', 'fromEvent', function(Event $e) {
            $event = $e->getParam('event');
            $sender = $e->getParam('sender');

            if ($event instanceof AggregateChanged) {
                $messageHeader = new MessageHeader(
                    $event->uuid(),
                    $event->occurredOn(),
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

            $event = $eventClass::reconstitute(
                $aggregateId,
                $payload,
                $message->header()->uuid(),
                $message->header()->createdOn(),
                $message->header()->version()
            );

            return $event;
        });

        $config = array(
            "adapter" => array(
                'type' => "Prooph\EventStore\Adapter\InMemoryAdapter",
            ),
            "feature_manager" => array(
                "factories" => array(
                    "persisted_event_dispatcher" => function ($fm) use ($serviceBusManager) {
                        return new PersistedEventDispatcher($serviceBusManager);
                    }
                )
            ),
            "features" => array(
                "persisted_event_dispatcher"
            )
        );

        $esConfig = new Configuration($config);

        $eventStore = new EventStore($esConfig);

        $userRepo = new AggregateRepository($eventStore, new AggregateTranslator(), new AggregateStreamStrategy($eventStore));

        $eventStore->beginTransaction();

        $user = \Prooph\EventSourcingTest\Mock\User::nameNew('Alex');

        $userRepo->addAggregateRoot($user);

        $eventStore->commit();

        $this->assertInstanceOf('Prooph\ServiceBus\Event\AbstractEvent', $userCreatedEventReceived);

        $this->assertEquals($user->id(), $userCreatedEventReceived->payload()['aggregate_id']);
        $this->assertEquals(array(
            'id' => $user->id(),
            'name' => 'Alex',
            'aggregate_id' => $user->id()),
            $userCreatedEventReceived->payload()
        );
        $this->assertEquals(1, $userCreatedEventReceived->version());
        $this->assertInstanceOf('\DateTime', $userCreatedEventReceived->occurredOn());
        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $userCreatedEventReceived->uuid());
    }
}
 