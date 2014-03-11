<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:14
 */

namespace Codeliner\ServiceBus\Service;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Message\DefaultQueueFactory;
use Codeliner\ServiceBus\Message\QueueInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;

/**
 * Class QueueManager
 *
 * @method QueueInterface get($name) Get Queue by name or alias
 *
 * @package Codeliner\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class QueueManager extends AbstractPluginManager
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
                'Queue must be instance of Codeliner\ServiceBus\Message\QueueInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 