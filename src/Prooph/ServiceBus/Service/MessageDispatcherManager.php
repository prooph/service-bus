<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 18:52
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageDispatcherInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Class MessageDispatcherManager
 *
 * @method MessageDispatcherInterface get($name) Get MessageDispatcher by name or alias
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class MessageDispatcherManager extends AbstractPluginManager
{
    protected $invokableClasses = array(
        'inmemorymessagedispatcher'      => 'Prooph\ServiceBus\Message\InMemoryMessageDispatcher',
    );

    protected $factories = array(
        'phpresquemessagedispatcher'     => 'Prooph\ServiceBus\Message\PhpResque\PhpResqueMessageDispatcherFactory',
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
                'MessageDispatcher must be instance of Prooph\ServiceBus\Message\MessageDispatcherInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
