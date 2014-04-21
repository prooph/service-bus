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

use Prooph\EventStore\Configuration\Configuration;
use Prooph\EventStore\EventStore;
use Prooph\ServiceBus\EventStoreFeature\EventStoreConnector;
use Prooph\ServiceBus\EventStoreFeature\PersistedEventDispatcher;
use Prooph\ServiceBus\Initializer\LocalSynchronousInitializer;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\User;
use Prooph\ServiceBusTest\Mock\UserCreated;
use Prooph\ServiceBusTest\TestCase;

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
        $serviceBusManager = new ServiceBusManager();

        $localEnv = new LocalSynchronousInitializer();

        $userCreatedEventReceived = false;

        $localEnv->addEventHandler(
            'Prooph\ServiceBusTest\Mock\UserCreated',
            function(UserCreated $e) use (&$userCreatedEventReceived) {
                $userCreatedEventReceived = $e;
            }
        );

        $serviceBusManager->events()->attach($localEnv);
        $serviceBusManager->events()->attach(new EventStoreConnector());

        $config = array(
            "adapter" => array(
                "Prooph\EventStore\Adapter\Zf2\Zf2EventStoreAdapter" => array(
                    'connection' => array(
                        'driver' => 'Pdo_Sqlite',
                        'database' => ':memory:'
                    )
                )
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

        $eventStore->getAdapter()->createSchema(array("User"));

        $eventStore->beginTransaction();

        $user = new User("Alex");

        $eventStore->attach($user);

        $eventStore->commit();

        $this->assertInstanceOf('Prooph\ServiceBusTest\Mock\UserCreated', $userCreatedEventReceived);

        $this->assertEquals($user->id(), $userCreatedEventReceived->aggregateId());
        $this->assertEquals(array('name' => 'Alex'), $userCreatedEventReceived->payload());
        $this->assertEquals(1, $userCreatedEventReceived->version());
        $this->assertInstanceOf('ValueObjects\DateTime\DateTime', $userCreatedEventReceived->occurredOn());
        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $userCreatedEventReceived->uuid());
    }
}
 