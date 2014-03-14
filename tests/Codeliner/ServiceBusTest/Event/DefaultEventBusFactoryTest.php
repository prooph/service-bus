<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.03.14 - 16:36
 */

namespace Codeliner\ServiceBusTest\Event;

use Codeliner\ServiceBus\Event\DefaultEventBusFactory;
use Codeliner\ServiceBus\Message\Queue;
use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\EventBusManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\OnEventHandler;
use Codeliner\ServiceBusTest\Mock\SomethingDone;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class DefaultEventBusFactoryTest
 *
 * @package Codeliner\ServiceBusTest\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
            Definition::CONFIG_ROOT => array(
                Definition::EVENT_BUS => array(
                    //name of the bus, must match with the Message.header.sender
                    'test-case-bus' => array(
                        Definition::EVENT_MAP => array(
                            //SomethingDone event is mapped to the OnEventHandler alias
                            'Codeliner\ServiceBusTest\Mock\SomethingDone' => 'something_done_handler'
                        ),
                        //Configure two queues, something_done_handler should be invoked two times
                        Definition::QUEUE => array('local', 'local-2'),
                        Definition::MESSAGE_DISPATCHER => Definition::IN_MEMORY_MESSAGE_DISPATCHER,
                    )
                ),
            )
        );

        //Add global config as service
        $this->serviceBusManager->setService('configuration', $config);

        //Should handle the SomethingDone event
        $this->somethingDoneHandler = new OnEventHandler();

        //Register DoSomethingHandler as Service
        $this->serviceBusManager->setService('something_done_handler', $this->somethingDoneHandler);

        $inMemoryMessageDispatcher = $this->serviceBusManager->get(Definition::MESSAGE_DISPATCHER_MANAGER)
            ->get(Definition::IN_MEMORY_MESSAGE_DISPATCHER);

        $inMemoryMessageDispatcher->registerEventReceiverManagerForQueue(
            new Queue('local'),
            $this->serviceBusManager->get(Definition::EVENT_RECEIVER_MANAGER)
        );

        $inMemoryMessageDispatcher->registerEventReceiverManagerForQueue(
            new Queue('local-2'),
            $this->serviceBusManager->get(Definition::EVENT_RECEIVER_MANAGER)
        );
    }

    /**
     * @test
     */
    public function it_creates_a_fully_configured_event_bus()
    {
        $eventBusManager = new EventBusManager();
        $eventBusManager->setServiceLocator($this->serviceBusManager);

        $factory = new DefaultEventBusFactory();

        $eventBus = $factory->createServiceWithName($eventBusManager, 'testcasebus', 'test-case-bus');

        $somethingDone = SomethingDone::fromData('test payload');

        $eventBus->publish($somethingDone);

        $this->assertEquals('test payload', $this->somethingDoneHandler->lastEvent()->data());
        $this->assertEquals(2, $this->somethingDoneHandler->eventCount());
    }
}
