<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:17
 */

namespace Prooph\ServiceBusTest\Service;

use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\EventReceiverLoader;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class EventReceiverLoaderTest
 *
 * @package Prooph\ServiceBusTest\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventReceiverLoaderTest extends TestCase
{
    /**
     * @var ServiceBusManager
     */
    private $serviceBusManager;

    /**
     * @var EventReceiverLoader
     */
    private $eventReceiverLoader;

    protected function setUp()
    {
        $this->serviceBusManager = new ServiceBusManager();

        $config = array(
            Definition::EVENT_BUS => array(
                //name of the bus, must match with the Message.header.sender
                'test-case-bus' => array(
                    Definition::EVENT_MAP => array(
                        //DoSomething command is mapped to the DoSometingHandler alias
                        'Prooph\ServiceBusTest\Mock\SomethingDone' => 'something_done_handler'
                    )
                )
            )
        );

        //Add global config as service
        $this->serviceBusManager->setService('configuration', $config);

        $this->eventReceiverLoader = new EventReceiverLoader();

        //Set MainServiceManager as ServiceLocator for the CommandReceiverLoader
        $this->eventReceiverLoader->setServiceLocator($this->serviceBusManager);
    }

    /**
     * @test
     */
    public function it_returns_the_default_event_receiver()
    {
        $eventReceiver = $this->eventReceiverLoader->get('test-case-bus');

        $this->assertInstanceOf('Prooph\ServiceBus\Event\EventReceiver', $eventReceiver);
    }
}
