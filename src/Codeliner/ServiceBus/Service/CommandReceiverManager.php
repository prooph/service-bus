<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 19:39
 */

namespace Codeliner\ServiceBus\Service;

use Codeliner\ServiceBus\Command\CommandReceiverInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Class CommandReceiverManager
 *
 * @package Codeliner\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandReceiverManager extends AbstractPluginManager
{
    protected $abstractFactories = array(
        'Codeliner\ServiceBus\Command\DefaultCommandReceiverFactory'
    );

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
                'CommandReceiver must be instance of Codeliner\ServiceBus\Command\CommandReceiverInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 