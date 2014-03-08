<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:51
 */

namespace Codeliner\ServiceBus\Command;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Message\MessageInterface;
use Codeliner\ServiceBus\Service\InvokeStrategyManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CommandReceiver
 *
 * @package Codeliner\ServiceBus\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandReceiver implements CommandReceiverInterface
{
    /**
     * @var array
     */
    protected $commandMap = array();

    /**
     * @var CommandFactoryInterface
     */
    protected $commandFactory;

    /**
     * @var ServiceLocatorInterface
     */
    protected $commandHandlerLocator;

    /**
     * @var array
     */
    protected $invokeStrategies = array('callback_strategy');

    /**
     * @var ServiceLocatorInterface
     */
    protected $invokeStrategyManager;

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
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException
     * @return void
     */
    public function handle(MessageInterface $aMessage)
    {
        if (!isset($this->commandMap[$aMessage->name()])) {
            return;
        }

        $command = $this->getCommandFactory()->fromMessage($aMessage);

        $handler = $this->commandHandlerLocator->get($this->commandMap[$aMessage->name()]);

        $invokeStrategy = null;

        foreach ($this->getInvokeStrategies() as $invokeStrategyName) {
            $invokeStrategy = $this->getInvokeStrategyManager()->get($invokeStrategyName);

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
    }

    /**
     * @param CommandFactoryInterface $aCommandFactory
     */
    public function setCommandFactory(CommandFactoryInterface $aCommandFactory)
    {
        $this->commandFactory = $aCommandFactory;
    }

    /**
     * @return CommandFactoryInterface
     */
    public function getCommandFactory()
    {
        if (is_null($this->commandFactory)) {
            $this->commandFactory = new CommandFactory();
        }

        return $this->commandFactory;
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
     * @param ServiceLocatorInterface $anInvokeStrategyManager
     */
    public function setInvokeStrategyManager(ServiceLocatorInterface $anInvokeStrategyManager)
    {
        $this->invokeStrategyManager = $anInvokeStrategyManager;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getInvokeStrategyManager()
    {
        if (is_null($this->invokeStrategyManager)) {
            $this->invokeStrategyManager = new InvokeStrategyManager();
        }

        return $this->invokeStrategyManager;
    }
}
