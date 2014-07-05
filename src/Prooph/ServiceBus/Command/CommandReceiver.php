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

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Service\CommandFactoryLoader;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\InvokeStrategyLoader;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CommandReceiver
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandReceiver implements CommandReceiverInterface
{
    /**
     * @var array
     */
    protected $commandMap = array();

    /**
     * @var CommandFactoryLoader
     */
    protected $commandFactoryLoader;

    /**
     * @var ServiceLocatorInterface
     */
    protected $commandHandlerLocator;

    /**
     * @var array
     */
    protected $invokeStrategies = array('callback_strategy', 'handle_command_strategy');

    /**
     * @var ServiceLocatorInterface
     */
    protected $invokeStrategyLoader;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @param array                   $aCommandMap
     * @param ServiceLocatorInterface $aCommandHandlerLocator
     */
    public function __construct(array $aCommandMap, ServiceLocatorInterface $aCommandHandlerLocator)
    {
        $this->commandMap = $aCommandMap;
        $this->commandHandlerLocator = $aCommandHandlerLocator;
    }

    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return void
     */
    public function handle(MessageInterface $aMessage)
    {
        $results = $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('message' => $aMessage));

        if ($results->stopped()) {
            return;
        }


        if (!isset($this->commandMap[$aMessage->name()])) {
            return;
        }

        $command = $this->getCommandFactoryLoader()->get($aMessage->name())->fromMessage($aMessage);

        $handler = $this->commandHandlerLocator->get($this->commandMap[$aMessage->name()]);


        $params = compact('command', 'handler');

        $results = $this->events()->trigger('invoke_handler.pre', $this, $params);

        if ($results->stopped()) {
            return;
        }

        $invokeStrategy = null;

        foreach ($this->getInvokeStrategies() as $invokeStrategyName) {
            $invokeStrategy = $this->getInvokeStrategyLoader()->get($invokeStrategyName);

            if ($invokeStrategy->canInvoke($handler, $command)) {
                break;
            }

            $invokeStrategy = null;
        }

        if (is_null($invokeStrategy)) {
            throw new RuntimeException(sprintf(
                'No InvokeStrategy can invoke command %s on handler %s',
                get_class($command),
                get_class($handler)
            ));
        }

        $invokeStrategy->invoke($handler, $command);

        $this->events()->trigger('invoke_handler.post', $this, $params);

        $params['message'] = $aMessage;

        $this->events()->trigger(__FUNCTION__. '.post', $this, $params);
    }

    /**
     * @param CommandFactoryLoader $aCommandFactoryLoader
     */
    public function setCommandFactoryLoader(CommandFactoryLoader $aCommandFactoryLoader)
    {
        $this->commandFactoryLoader = $aCommandFactoryLoader;
    }

    /**
     * @return CommandFactoryLoader
     */
    public function getCommandFactoryLoader()
    {
        return $this->commandFactoryLoader;
    }

    /**
     * @param array $anInvokeStrategies
     */
    public function setInvokeStrategies(array $anInvokeStrategies)
    {
        $this->invokeStrategies = $anInvokeStrategies;
    }

    /**
     * @return array
     */
    public function getInvokeStrategies()
    {
        return $this->invokeStrategies;
    }

    /**
     * @param ServiceLocatorInterface $anInvokeStrategyLoader
     */
    public function setInvokeStrategyLoader(ServiceLocatorInterface $anInvokeStrategyLoader)
    {
        $this->invokeStrategyLoader = $anInvokeStrategyLoader;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getInvokeStrategyLoader()
    {
        if (is_null($this->invokeStrategyLoader)) {
            $this->invokeStrategyLoader = new InvokeStrategyLoader();
        }

        return $this->invokeStrategyLoader;
    }

    public function events()
    {
        if (is_null($this->events)) {
            $this->events = new EventManager(array(
                Definition::SERVICE_BUS_COMPONENT,
                'command_receiver',
                __CLASS__
            ));
        }

        return $this->events;
    }
}
