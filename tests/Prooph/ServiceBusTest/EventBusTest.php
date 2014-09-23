<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 23.09.14 - 20:48
 */

namespace Prooph\ServiceBusTest;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\InvokeStrategy\ForwardToMessageDispatcherStrategy;
use Prooph\ServiceBus\Message\FromMessageTranslator;
use Prooph\ServiceBus\Message\InMemoryMessageDispatcher;
use Prooph\ServiceBus\Message\ToMessageTranslator;
use Prooph\ServiceBus\Router\EventRouter;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\Mock\SomethingDoneInvokeStrategy;
use Prooph\ServiceBusTest\Mock\SomethingDoneListener;

/**
 * Class EventBusTest
 *
 * @package Prooph\ServiceBusTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventBusTest extends TestCase
{
    /**
     * @var SomethingDoneListener
     */
    protected $somethingDoneListener;

    /**
     * @var EventBus
     */
    protected $eventBus;

    protected function setUp()
    {
        $this->somethingDoneListener = new SomethingDoneListener();

        $this->eventBus = new EventBus();

        $router = new EventRouter();

        //Route the event to a message dispatcher which then dispatches the message on a second bus
        $router->route('Prooph\ServiceBusTest\Mock\SomethingDone')->to($this->setUpMessageDispatcher());

        $this->eventBus->utilize($router);

        //Register message forwarder which translates command to message and forward it to the message dispatcher
        $this->eventBus->utilize(new ForwardToMessageDispatcherStrategy(new ToMessageTranslator()));
    }

    /**
     * @return InMemoryMessageDispatcher
     */
    protected function setUpMessageDispatcher()
    {
        $eventBus = new EventBus();

        //Translate message back to event
        $eventBus->utilize(new FromMessageTranslator());

        $router = new EventRouter();

        $router->route('Prooph\ServiceBusTest\Mock\SomethingDone')->to($this->somethingDoneListener);

        $eventBus->utilize($router);

        $eventBus->utilize(new SomethingDoneInvokeStrategy());

        //Set up message dispatcher with a prepared command bus that can dispatch the message to command handler
        $messageDispatcher = new InMemoryMessageDispatcher(new CommandBus(), $eventBus);

        return $messageDispatcher;
    }

    /**
     * @test
     */
    public function it_forwards_an_event_to_message_dispatcher_and_than_to_listener()
    {
        $somethingDone = SomethingDone::fromData('dispatch me');

        $this->eventBus->dispatch($somethingDone);

        $this->assertEquals(array('data' => 'dispatch me'), $this->somethingDoneListener->lastEvent()->payload());
    }
}
 