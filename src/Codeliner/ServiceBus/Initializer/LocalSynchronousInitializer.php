<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.03.14 - 22:25
 */

namespace Codeliner\ServiceBus\Initializer;

use Codeliner\ServiceBus\Command\CommandInterface;
use Codeliner\ServiceBus\Event\EventInterface;
use Codeliner\ServiceBus\Service\Definition;
use Zend\EventManager\EventInterface as ZendEventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * Class LocalSynchronousInitializer
 *
 * @package Codeliner\ServiceBus\Initializer
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class LocalSynchronousInitializer implements ListenerAggregateInterface
{
    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * @var array
     */
    protected $commandHandlers = array();

    /**
     * @var array
     */
    protected $eventHandlers = array();

    /**
     * @param CommandInterface|string $aCommand
     * @param mixed                   $aCommandHandler
     */
    public function setCommandHandler($aCommand, $aCommandHandler)
    {
        if ($aCommand instanceof CommandInterface) {
            $aCommand = get_class($aCommand);
        }

        \Assert\that($aCommand)->notEmpty()->string();

        $this->commandHandlers[$aCommand] = $aCommandHandler;
    }

    /**
     * @param EventInterface|string $anEvent
     * @param mixed                 $anEventHandler
     */
    public function addEventHandler($anEvent, $anEventHandler)
    {
        if ($anEvent instanceof EventInterface) {
            $anEvent = get_class($anEvent);
        }

        \Assert\that($anEvent)->notEmpty()->string();

        if (! isset($this->eventHandlers[$anEvent])) {
            $this->eventHandlers[$anEvent] = array();
        }

        $this->eventHandlers[$anEvent][] = $anEventHandler;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('initialize', array($this, 'initializeLocalServiceBus'));
    }

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * @param EventInterface $e
     */
    public function initializeLocalServiceBus(ZendEventInterface $e)
    {
        /* @var $serviceBusManager \Codeliner\ServiceBus\Service\ServiceBusManager */
        $serviceBusManager = $e->getTarget();

        $serviceBusManager->setAllowOverride(true);

        if ($serviceBusManager->has('configuration')) {
            $configuration = $serviceBusManager->get('configuration');
        } else {
            $configuration = array();
        }

        if (!isset($configuration[Definition::CONFIG_ROOT])) {
            $configuration[Definition::CONFIG_ROOT] = array();
        }

        if (!isset($configuration[Definition::CONFIG_ROOT][Definition::COMMAND_BUS])) {
            $configuration[Definition::CONFIG_ROOT][Definition::COMMAND_BUS] = array();
        }

        if (!isset($configuration[Definition::CONFIG_ROOT][Definition::EVENT_BUS])) {
            $configuration[Definition::CONFIG_ROOT][Definition::EVENT_BUS] = array();
        }

        $serviceBusManager->setDefaultCommandBus('local-command-bus');
        $serviceBusManager->setDefaultEventdBus('local-event-bus');

        $commandMap = array();

        foreach ($this->commandHandlers as $commandName => $commandHandler) {
            $serviceBusManager->setService($commandName . '_local_handler', $commandHandler);
            $commandMap[$commandName] = $commandName . '_local_handler';
        }

        $configuration[Definition::CONFIG_ROOT][Definition::COMMAND_BUS]['local-command-bus'] = array(
            Definition::COMMAND_MAP        => $commandMap,
            Definition::QUEUE              => 'local-queue',
            Definition::MESSAGE_DISPATCHER => 'in_memory_message_dispatcher'
        );

        $eventMap = array();

        foreach ($this->eventHandlers as $eventName => $handlersOfEvent) {
            $eventMap[$eventName] = array();

            foreach ($handlersOfEvent as $handlerIndex => $eventHandler) {
                $serviceBusManager->setService($eventName . '_local_handler_' . $handlerIndex, $eventHandler);
                $eventMap[$eventName][] = $eventName . '_local_handler_' . $handlerIndex;
            }
        }

        $configuration[Definition::CONFIG_ROOT][Definition::EVENT_BUS]['local-event-bus'] = array(
            Definition::EVENT_MAP => $eventMap,
            Definition::QUEUE              => 'local-queue',
            Definition::MESSAGE_DISPATCHER => 'in_memory_message_dispatcher'
        );

        $serviceBusManager->setService('configuration', $configuration);

        /* @var $messageDispatcher \Codeliner\ServiceBus\Message\InMemoryMessageDispatcher */
        $messageDispatcher = $serviceBusManager->get('message_dispatcher_manager')->get('in_memory_message_dispatcher');

        $commandReceiverManager = $serviceBusManager->get('command_receiver_manager');

        $eventReceiverManager   = $serviceBusManager->get('event_receiver_manager');

        $queue = $serviceBusManager->get('queue_manager')->get('local-queue');

        $messageDispatcher->registerCommandReceiverManagerForQueue($queue, $commandReceiverManager);
        $messageDispatcher->registerEventReceiverManagerForQueue($queue, $eventReceiverManager);

        $serviceBusManager->setAllowOverride(false);
    }
}
 