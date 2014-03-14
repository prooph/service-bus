<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.03.14 - 23:31
 */

namespace Codeliner\ServiceBusTest\Initializer;

use Codeliner\ServiceBus\Event\EventInterface;
use Codeliner\ServiceBus\Initializer\LocalSynchronousInitializer;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\Mock\DoSomething;
use Codeliner\ServiceBusTest\Mock\HandleCommandHandler;
use Codeliner\ServiceBusTest\Mock\OnEventHandler;
use Codeliner\ServiceBusTest\Mock\SomethingDone;
use Codeliner\ServiceBusTest\TestCase;
use Zend\EventManager\GlobalEventManager;
use Zend\EventManager\StaticEventManager;

/**
 * Class LocalInitializerTest
 *
 * @package Codeliner\ServiceBusTest\Initializer
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class LocalInitializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_initializes_a_local_service_bus_environment()
    {
        $serviceBusManager = new ServiceBusManager();

        $localEnv = new LocalSynchronousInitializer();

        $doSomething = DoSomething::fromData('test payload');
        $somethingDone = SomethingDone::fromData('event payload');

        $doSomethingHandler = new HandleCommandHandler();
        $somethingDoneHandler = new OnEventHandler();

        $localEnv->setCommandHandler($doSomething, $doSomethingHandler);
        $localEnv->addEventHandler($somethingDone, $somethingDoneHandler);
        $localEnv->addEventHandler($somethingDone, $somethingDoneHandler);

        $serviceBusManager->events()->attachAggregate($localEnv);

        $serviceBusManager->initialize();

        $serviceBusManager->getCommandBus()->send($doSomething);
        $serviceBusManager->getEventBus()->publish($somethingDone);

        $this->assertEquals('test payload', $doSomethingHandler->lastCommand()->data());
        $this->assertEquals('event payload', $somethingDoneHandler->lastEvent()->data());
        $this->assertEquals(2, $somethingDoneHandler->eventCount());
    }
}
 