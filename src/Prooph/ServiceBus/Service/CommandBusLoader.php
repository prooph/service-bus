<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:39
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Command\CommandBusInterface;
use Prooph\ServiceBus\Command\DefaultCommandBusFactory;
use Prooph\ServiceBus\Exception\RuntimeException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception;

/**
 * Class CommandBusLoader
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandBusLoader extends AbstractPluginManager
{
    /**
     * @param ConfigInterface $aConfig
     */
    public function __construct(ConfigInterface $aConfig = null)
    {
        parent::__construct($aConfig);

        $this->abstractFactories[] = new DefaultCommandBusFactory();
    }

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if (! $plugin instanceof CommandBusInterface) {
            throw new RuntimeException(sprintf(
                'CommandBus must be instance of Prooph\ServiceBus\Command\CommandBusInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 