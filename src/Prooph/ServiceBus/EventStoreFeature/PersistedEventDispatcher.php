<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.04.14 - 00:40
 */

namespace Prooph\ServiceBus\EventStoreFeature;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Feature\FeatureInterface;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Prooph\ServiceBus\Service\ServiceBusManager;

/**
 * Class PersistedEventDispatcher
 *
 * @package Prooph\ServiceBus\EventStoreFeature
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PersistedEventDispatcher implements FeatureInterface
{
    /**
     * @var ServiceBusManager
     */
    protected $serviceBusManager;

    /**
     * @param ServiceBusManager $serviceBusManager
     */
    public function __construct(ServiceBusManager $serviceBusManager)
    {
        $this->serviceBusManager = $serviceBusManager;
    }


    /**
     * @param EventStore $eventStore
     * @return void
     */
    public function setUp(EventStore $eventStore)
    {
        $eventStore->getPersistenceEvents()->attach('commit.post', array($this, "onPostCommit"));
    }

    /**
     * @param PostCommitEvent $e
     */
    public function onPostCommit(PostCommitEvent $e)
    {
        foreach ($e->getPersistedEvents() as $persistedEvent) {
            $this->serviceBusManager->route($persistedEvent);
        }
    }
}
 