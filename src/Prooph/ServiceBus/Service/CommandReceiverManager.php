<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 19:39
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Command\CommandReceiverInterface;
use Prooph\ServiceBus\Command\DefaultCommandReceiverFactory;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception;

/**
 * Class CommandReceiverManager
 *
 * @method CommandReceiverInterface get($name) Get CommandReceiver by name or alias
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandReceiverManager extends AbstractPluginManager
{
    /**
     * @param ConfigInterface $aConfig
     */
    public function __construct(ConfigInterface $aConfig = null)
    {
        parent::__construct($aConfig);

        $this->abstractFactories[] = new DefaultCommandReceiverFactory();
    }

    /**
     * Validate the plugin
     *
     * @param  mixed $plugin
     * @return void
     * @throws \RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if (! $plugin instanceof CommandReceiverInterface) {
            throw new \RuntimeException(sprintf(
                'CommandReceiver must be instance of Prooph\ServiceBus\Command\CommandReceiverInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 