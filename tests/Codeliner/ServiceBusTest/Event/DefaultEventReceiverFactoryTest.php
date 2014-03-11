<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:25
 */

namespace Codeliner\ServiceBusTest\Event;

use Codeliner\ServiceBus\Event\DefaultEventReceiverFactory;
use Codeliner\ServiceBus\Event\EventFactory;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Message\StandardMessage;
use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\EventReceiverManager;
use Codeliner\ServiceBus\Service\InvokeStrategyManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\SomethingDoneHandler;
use Codeliner\ServiceBusTest\Mock\SomethingDoneInvokeStrategy;
use Codeliner\ServiceBusTest\TestCase;
use Rhumsaa\Uuid\Uuid;

/**
 * Class DefaultEventReceiverFactoryTest
 *
 * @package Codeliner\ServiceBusTest\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DefaultEventReceiverFactoryTest extends TestCase
{
    /**
     * @var ServiceBusManager
     */
    private $serviceBusManager;

    /**
     * @var SomethingDoneHandler
     */
    private $somethingDoneHandler;

    /**
     * @var EventReceiverManager
     */
    private $eventReceiverManager;

    protected function setUp()
    {
        $this->serviceBusManager = new ServiceBusManager();

        $config = array(
            Definition::CONFIG_ROOT => array(
                Definition::EVENT_BUS => array(
                    //name of the bus, must match with the Message.header.sender
                    'test-case-bus' => array(
                        Definition::EVENT_MAP => array(
                            //SomethingDone event is mapped to the SomethingDoneHandler alias
                            'Codeliner\ServiceBusTest\Mock\SomethingDone' => 'something_done_handler'
                        )
                    )
                ),
                Definition::EVENT_HANDLER_INVOKE_STRATEGIES => array(
                    //Alias of the SomethingDoneInvokeStrategy
                    'something_done_invoke_strategy'
                )
            )
        );

        //Add global config as service
        $this->serviceBusManager->setService('configuration', $config);

        //Should handle the SomethingDone event
        $this->somethingDoneHandler = new SomethingDoneHandler();

        //Register SomethingDoneHandler as Service
        $this->serviceBusManager->setService('something_done_handler', $this->somethingDoneHandler);

        $invokeStrategyManager = new InvokeStrategyManager();

        //Register DoSomethingInvokeStrategy as Service
        $invokeStrategyManager->setService('something_done_invoke_strategy', new SomethingDoneInvokeStrategy());

        $this->serviceBusManager->setAllowOverride(true);

        //Register InvokeStrategyManager as Service
        $this->serviceBusManager->setService(Definition::INVOKE_STRATEGY_MANAGER, $invokeStrategyManager);

        //Register EventFactory as Service, this is not necessary but we do it for testing purposes
        $this->serviceBusManager->setService(Definition::EVENT_FACTORY, new EventFactory());

        $this->eventReceiverManager = new EventReceiverManager();

        //Set MainServiceManager as ServiceLocator for the CommandReceiverManager
        $this->eventReceiverManager->setServiceLocator($this->serviceBusManager);
    }

    /**
     * @test
     */
    public function it_can_create_an_event_receiver()
    {
        $defaultEventReceiverFactory = new DefaultEventReceiverFactory();

        $this->assertTrue(
            $defaultEventReceiverFactory
                ->canCreateServiceWithName($this->eventReceiverManager, 'testcasebus', 'test-case-bus')
        );
    }

    /**
     * @test
     */
    public function it_creates_a_fully_configured_event_receiver()
    {
        $defaultEventReceiverFactory = new DefaultEventReceiverFactory();

        $eventReceiver = $defaultEventReceiverFactory
            ->createServiceWithName($this->eventReceiverManager, 'testcasebus', 'test-case-bus');

        $this->assertSame(
            $this->serviceBusManager->get(Definition::EVENT_FACTORY),
            $eventReceiver->getEventFactory()
        );

        $this->assertSame(
            $this->serviceBusManager->get(Definition::INVOKE_STRATEGY_MANAGER),
            $eventReceiver->getInvokeStrategyManager()
        );

        $this->assertEquals(
            array('something_done_invoke_strategy'),
            $eventReceiver->getInvokeStrategies()
        );

        $message = new StandardMessage(
            'Codeliner\ServiceBusTest\Mock\SomethingDone',
            new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case-bus'),
            array('data' => 'test payload')
        );

        $eventReceiver->handle($message);

        $this->assertEquals('test payload', $this->somethingDoneHandler->lastEvent()->data());
    }
}
 