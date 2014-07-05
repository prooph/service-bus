<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 23:14
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\InvokeStrategy\InvokeStrategyInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Class InvokeStrategyLoader
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class InvokeStrategyLoader extends AbstractPluginManager
{
    /**
     * @var array
     */
    protected $invokableClasses = array(
        'callbackstrategy'      => 'Prooph\ServiceBus\InvokeStrategy\CallbackStrategy',
        'handlecommandstrategy' => 'Prooph\ServiceBus\InvokeStrategy\HandleCommandStrategy',
        'oneventstrategy'       => 'Prooph\ServiceBus\InvokeStrategy\OnEventStrategy',
    );

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
        if (! $plugin instanceof InvokeStrategyInterface) {
            throw new RuntimeException(sprintf(
                'InvokeStrategy must be instance of Prooph\ServiceBus\InvokeStrategy\InvokeStrategyInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 