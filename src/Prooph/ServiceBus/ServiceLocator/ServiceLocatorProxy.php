<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 23.09.14 - 20:56
 */

namespace Prooph\ServiceBus\ServiceLocator;

use Prooph\Common\ServiceLocator\ServiceLocator;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Process\EventDispatch;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Class ServiceLocatorProxy
 *
 * @package Prooph\ServiceBus\ServiceLocator
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ServiceLocatorProxy extends AbstractListenerAggregate
{
    /**
     * @var ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocator $serviceLocator
     */
    public function __construct(ServiceLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
    /**
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(CommandDispatch::LOCATE_HANDLER, array($this, 'onLocateCommandHandler'));
        $events->attach(EventDispatch::LOCATE_LISTENER, array($this, 'onLocateEventListener'));
    }

    public function onLocateCommandHandler(CommandDispatch $commandDispatch)
    {
        $commandHandlerAlias = $commandDispatch->getCommandHandler();

        if (is_string($commandHandlerAlias) && $this->serviceLocator->has($commandHandlerAlias)) {
            $commandDispatch->setCommandHandler($this->serviceLocator->get($commandHandlerAlias));
        }
    }

    public function onLocateEventListener(EventDispatch $eventDispatch)
    {
        $eventListenerAlias = $eventDispatch->getCurrentEventListener();

        if (is_string($eventListenerAlias) && $this->serviceLocator->has($eventListenerAlias)) {
            $eventDispatch->setCurrentEventListener($this->serviceLocator->get($eventListenerAlias));
        }
    }
}
 