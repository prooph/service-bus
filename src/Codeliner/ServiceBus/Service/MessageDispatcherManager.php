<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 18:52
 */

namespace Codeliner\ServiceBus\Service;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Message\MessageDispatcherInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Class MessageDispatcherManager
 *
 * @package Codeliner\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class MessageDispatcherManager extends AbstractPluginManager
{
    protected $invokableClasses = array(
        'inmemorymessagedispatcher'      => 'Codeliner\ServiceBus\Message\InMemoryMessageDispatcher',
    );

    /**
     * Validate the plugin
     *
     * @param  mixed $plugin
     * @return void
     * @throws RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if (! $plugin instanceof MessageDispatcherInterface) {
            throw new RuntimeException(sprintf(
                'MessageDispatcher must be instance of Codeliner\ServiceBus\Message\MessageDispatcherInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
