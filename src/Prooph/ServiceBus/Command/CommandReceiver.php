<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:51
 */

namespace Prooph\ServiceBus\Command;

use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class CommandReceiver
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandReceiver implements CommandReceiverInterface
{
    /**
     * @var ServiceBusManager
     */
    protected $serviceBusManager;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @param ServiceBusManager $serviceBusManager
     */
    public function __construct(ServiceBusManager $serviceBusManager)
    {
        $this->serviceBusManager = $serviceBusManager;
    }

    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return void
     */
    public function handle(MessageInterface $aMessage)
    {
        $params = array('message' => $aMessage);

        $results = $this->events()->trigger(__FUNCTION__ . '.pre', $this, $params);

        if ($results->stopped()) {
            return;
        }

        $command = $this->serviceBusManager->getCommandFactoryLoader()
            ->getCommandFactoryFor($aMessage->name())
            ->fromMessage($aMessage);

        $this->serviceBusManager->routeDirect($command);

        $params['command'] = $command;

        $this->events()->trigger(__FUNCTION__. '.post', $this, $params);
    }

    /**
     * @return EventManager
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * @param EventManagerInterface $events
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            Definition::SERVICE_BUS_COMPONENT,
            'command_receiver',
            __CLASS__
        ));

        $this->events = $events;
    }
}
