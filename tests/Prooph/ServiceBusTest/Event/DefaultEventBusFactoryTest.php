<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.03.14 - 16:36
 */

namespace Prooph\ServiceBusTest\Event;

use Prooph\ServiceBus\Event\DefaultEventBusFactory;
use Prooph\ServiceBus\Message\Queue;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\EventBusLoader;
use Prooph\ServiceBus\Service\MessageFactoryLoader;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\OnEventHandler;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class DefaultEventBusFactoryTest
 *
 * @package Prooph\ServiceBusTest\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DefaultEventBusFactoryTest extends TestCase
{
    /**
     * @var ServiceBusManager
     */
    private $serviceBusManager;

    /**
     * @var OnEventHandler
     */
    private $somethingDoneHandler;

    protected function setUp()
    {
        $this->serviceBusManager = new ServiceBusManager();

        $config = array(
            Definition::EVENT_BUS => array(
                //name of the bus, must match with the Message.header.sender
                'test-case-bus' => array(
                    Definition::EVENT_MAP => array(
                        //SomethingDone event is mapped to the OnEventHandler alias
                        'Prooph\ServiceBusTest\Mock\SomethingDone' => 'something_done_handler'
                    ),
                    //Configure two queues, something_done_handler should be invoked two times
                    Definition::QUEUE => array('local', 'local-2'),
                    Definition::MESSAGE_DISPATCHER => Definition::IN_MEMORY_MESSAGE_DISPATCHER,
                )
            ),
        );

        //Add global config as service
        $this->serviceBusManager->setService('configuration', $config);

        //Should handle the SomethingDone event
        $this->somethingDoneHandler = new OnEventHandler();

        //Register DoSomethingHandler as Service
        $this->serviceBusManager->setService('something_done_handler', $this->somethingDoneHandler);

        $inMemoryMessageDispatcher = $this->serviceBusManager->get(Definition::MESSAGE_DISPATCHER_LOADER)
            ->get(Definition::IN_MEMORY_MESSAGE_DISPATCHER);

        $inMemoryMessageDispatcher->registerEventReceiverLoaderForQueue(
            new Queue('local'),
            $this->serviceBusManager->get(Definition::EVENT_RECEIVER_LOADER)
        );

        $inMemoryMessageDispatcher->registerEventReceiverLoaderForQueue(
            new Queue('local-2'),
            $this->serviceBusManager->get(Definition::EVENT_RECEIVER_LOADER)
        );
    }

    /**
     * @test
     */
    public function it_creates_a_fully_configured_event_bus()
    {
        $eventBusLoader = new EventBusLoader();
        $eventBusLoader->setServiceLocator($this->serviceBusManager);

        $factory = new DefaultEventBusFactory();

        $eventBus = $factory->createServiceWithName($eventBusLoader, 'testcasebus', 'test-case-bus');

        $eventBus->setMessageFactoryLoader(new MessageFactoryLoader());

        $somethingDone = SomethingDone::fromData('test payload');

        $eventBus->publish($somethingDone);

        $this->assertEquals('test payload', $this->somethingDoneHandler->lastEvent()->data());
        $this->assertEquals(2, $this->somethingDoneHandler->eventCount());
    }
}
