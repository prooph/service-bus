<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:15
 */

namespace Codeliner\ServiceBus\Service;

use Codeliner\ServiceBus\Event\DefaultEventReceiverFactory;
use Codeliner\ServiceBus\Event\EventReceiverInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;

/**
 * Class EventReceiverManager
 *
 * @package Codeliner\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventReceiverManager extends AbstractPluginManager
{
    /**
     * @param ConfigInterface $aConfig
     */
    public function __construct(ConfigInterface $aConfig = null)
    {
        parent::__construct($aConfig);

        $this->abstractFactories[] = new DefaultEventReceiverFactory();
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
        if (! $plugin instanceof EventReceiverInterface) {
            throw new \RuntimeException(sprintf(
                'EventReceiver must be instance of Codeliner\ServiceBus\Event\EventReceiverInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 