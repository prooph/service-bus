<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.03.14 - 23:31
 */

namespace Prooph\ServiceBusTest\Initializer;

use Prooph\ServiceBus\Initializer\LocalSynchronousInitializer;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\HandleCommandHandler;
use Prooph\ServiceBusTest\Mock\OnEventHandler;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class LocalInitializerTest
 *
 * @package Prooph\ServiceBusTest\Initializer
 * @author Alexander Miertsch <contact@prooph.de>
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
 