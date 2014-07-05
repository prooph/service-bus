<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.03.14 - 16:05
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Event\DefaultEventBusFactory;
use Prooph\ServiceBus\Event\EventBusInterface;
use Prooph\ServiceBus\Exception\RuntimeException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception;

/**
 * Class EventBusLoader
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventBusLoader extends AbstractPluginManager
{
    /**
     * @param ConfigInterface $aConfig
     */
    public function __construct(ConfigInterface $aConfig = null)
    {
        parent::__construct($aConfig);

        $this->abstractFactories[] = new DefaultEventBusFactory();
    }

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        if (! $plugin instanceof EventBusInterface) {
            throw new RuntimeException(sprintf(
                'EventBus must be instance of Prooph\ServiceBus\Command\EventBusInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 