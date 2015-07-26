<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 23:57
 */

namespace Prooph\ServiceBusTest;

use Prooph\Common\ServiceLocator\ZF2\Zf2ServiceManagerProxy;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\InvokeStrategy\ForwardToRemoteMessageDispatcherStrategy;
use Prooph\ServiceBus\Message\FromRemoteMessageTranslator;
use Prooph\ServiceBus\Message\InMemoryRemoteMessageDispatcher;
use Prooph\ServiceBus\Message\ProophDomainMessageToRemoteMessageTranslator;
use Prooph\ServiceBus\Router\CommandRouter;
use Prooph\ServiceBus\ServiceLocator\ServiceLocatorProxy;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\DoSomethingHandler;
use Prooph\ServiceBusTest\Mock\DoSomethingInvokeStrategy;
use Zend\ServiceManager\ServiceManager;

/**
 * Class CommandBusTest
 *
 * @package Prooph\ServiceBusTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandBusTest extends TestCase
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var DoSomethingHandler
     */
    protected $doSomethingHandler;

    protected function setUp()
    {
        $this->doSomethingHandler = new DoSomethingHandler();

        $this->commandBus = new CommandBus();

        $router = new CommandRouter();

        //Route the command to a message dispatcher which then dispatches the message on a second bus
        $router->route('Prooph\ServiceBusTest\Mock\DoSomething')->to($this->setUpMessageDispatcher());

        $this->commandBus->utilize($router);

        //Register message forwarder which translates command to message and forward it to the message dispatcher
        $this->commandBus->utilize(new ForwardToRemoteMessageDispatcherStrategy(new ProophDomainMessageToRemoteMessageTranslator()));
    }

    /**
     * @return InMemoryRemoteMessageDispatcher
     */
    protected function setUpMessageDispatcher()
    {
        $commandBus = new CommandBus();

        //Translate message back to command
        $commandBus->utilize(new FromRemoteMessageTranslator());

        $router = new CommandRouter();

        $router->route('Prooph\ServiceBusTest\Mock\DoSomething')->to('do_something_handler');

        $commandBus->utilize($router);

        //Set up a ZF2 ServiceLocator to locate the command handler
        //In this scenario it would be easier to route the command directly to the handler instance
        //but we want to test the full stack
        $sm = new ServiceManager();

        $sm->setService('do_something_handler', $this->doSomethingHandler);

        $commandBus->utilize(new ServiceLocatorProxy(Zf2ServiceManagerProxy::proxy($sm)));

        //Register appropriate invoke strategy
        $commandBus->utilize(new DoSomethingInvokeStrategy());

        //Set up message dispatcher with a prepared command bus that can dispatch the message to command handler
        $messageDispatcher = new InMemoryRemoteMessageDispatcher($commandBus, new EventBus());

        return $messageDispatcher;
    }

    /**
     * @test
     */
    public function it_forwards_a_command_to_message_dispatcher_and_than_to_handler()
    {
        $doSomething = DoSomething::fromData('dispatch me');

        $this->commandBus->dispatch($doSomething);

        $this->assertEquals(array('data' => 'dispatch me'), $this->doSomethingHandler->lastCommand()->payload());
    }
}
 