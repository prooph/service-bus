<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:14
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\DefaultQueueFactory;
use Prooph\ServiceBus\Message\QueueInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;

/**
 * Class QueueLoader
 *
 * @method QueueInterface get($name) Get Queue by name or alias
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class QueueLoader extends AbstractPluginManager
{
    /**
     * @param ConfigInterface $aConfig
     */
    public function __construct(ConfigInterface $aConfig = null)
    {
        parent::__construct($aConfig);

        $this->abstractFactories[] = new DefaultQueueFactory();
    }

    /**
     * Validate the plugin
     *
     * @param  mixed $plugin
     * @return void
     * @throws RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if (! $plugin instanceof QueueInterface) {
            throw new RuntimeException(sprintf(
                'Queue must be instance of Prooph\ServiceBus\Message\QueueInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 