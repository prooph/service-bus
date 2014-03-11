<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:17
 */

namespace Codeliner\ServiceBusTest\Service;

use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\EventReceiverManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class EventReceiverManagerTest
 *
 * @package Codeliner\ServiceBusTest\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventReceiverManagerTest extends TestCase
{
    /**
     * @var ServiceBusManager
     */
    private $serviceBusManager;

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
                            //DoSomething command is mapped to the DoSometingHandler alias
                            'Codeliner\ServiceBusTest\Mock\SomethingDone' => 'something_done_handler'
                        )
                    )
                )
            )
        );

        //Add global config as service
        $this->serviceBusManager->setService('configuration', $config);

        $this->eventReceiverManager = new EventReceiverManager();

        //Set MainServiceManager as ServiceLocator for the CommandReceiverManager
        $this->eventReceiverManager->setServiceLocator($this->serviceBusManager);
    }

    /**
     * @test
     */
    public function it_returns_the_default_event_receiver()
    {
        $eventReceiver = $this->eventReceiverManager->get('test-case-bus');

        $this->assertInstanceOf('Codeliner\ServiceBus\Event\EventReceiver', $eventReceiver);
    }
}
