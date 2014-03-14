<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.03.14 - 16:05
 */

namespace Codeliner\ServiceBus\Service;

use Codeliner\ServiceBus\Event\DefaultEventBusFactory;
use Codeliner\ServiceBus\Event\EventBusInterface;
use Codeliner\ServiceBus\Exception\RuntimeException;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception;

/**
 * Class EventBusManager
 *
 * @package Codeliner\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventBusManager extends AbstractPluginManager
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
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        if (! $plugin instanceof EventBusInterface) {
            throw new RuntimeException(sprintf(
                'EventBus must be instance of Codeliner\ServiceBus\Command\EventBusInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 