<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 23.09.14 - 21:21
 */

namespace Prooph\ServiceBusTest\ServiceLocator;

use Prooph\Common\ServiceLocator\ZF2\Zf2ServiceManagerProxy;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Process\EventDispatch;
use Prooph\ServiceBus\ServiceLocator\ServiceLocatorPlugin;
use Prooph\ServiceBusTest\TestCase;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ServiceLocatorProxyTest
 *
 * @package Prooph\ServiceBusTest\ServiceLocator
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ServiceLocatorProxyTest extends TestCase
{
    /**
     * @var ServiceLocatorPlugin
     */
    protected $serviceLocatorProxy;

    protected function setUp()
    {
        $config = new Config(array(
            'invokables' => array(
                'do_something_handler' => 'Prooph\ServiceBusTest\Mock\DoSomethingHandler',
                'something_done_listener' => 'Prooph\ServiceBusTest\Mock\SomethingDoneListener',
            )
        ));

        $sm = new ServiceManager($config);

        $this->serviceLocatorProxy = new ServiceLocatorPlugin(Zf2ServiceManagerProxy::proxy($sm));
    }

    /**
     * @test
     */
    public function it_locates_a_command_handler()
    {
        $commandDispatch = new CommandDispatch();

        $commandDispatch->setCommandHandler('do_something_handler');

        $commandDispatch->setName(CommandDispatch::LOCATE_HANDLER);

        $this->serviceLocatorProxy->onLocateCommandHandler($commandDispatch);

        $this->assertInstanceOf('Prooph\ServiceBusTest\Mock\DoSomethingHandler', $commandDispatch->getCommandHandler());
    }

    /**
     * @test
     */
    public function it_locates_an_event_listener()
    {
        $eventDispatch = new EventDispatch();

        $eventDispatch->setCurrentEventListener('something_done_listener');

        $eventDispatch->setName(EventDispatch::LOCATE_LISTENER);

        $this->serviceLocatorProxy->onLocateEventListener($eventDispatch);

        $this->assertInstanceOf('Prooph\ServiceBusTest\Mock\SomethingDoneListener', $eventDispatch->getCurrentEventListener());
    }
}
 