<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:15
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Event\DefaultEventReceiverFactory;
use Prooph\ServiceBus\Event\EventReceiverInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;

/**
 * Class EventReceiverLoader
 *
 * @method EventReceiverInterface get($name) Get EventReceiver by name or alias
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class EventReceiverLoader extends AbstractPluginManager
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
                'EventReceiver must be instance of Prooph\ServiceBus\Event\EventReceiverInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 