<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 23:14
 */

namespace Codeliner\ServiceBus\Service;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\InvokeStrategy\InvokeStrategyInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Class InvokeStrategyManager
 *
 * @package Codeliner\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class InvokeStrategyManager extends AbstractPluginManager
{
    /**
     * @var array
     */
    protected $invokableClasses = array(
        'callbackstrategy' => 'Codeliner\ServiceBus\InvokeStrategy\CallbackStrategy',
    );

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
        if (! $plugin instanceof InvokeStrategyInterface) {
            throw new RuntimeException(sprintf(
                'InvokeStrategy must be instance of Codeliner\ServiceBus\InvokeStrategy\InvokeStrategyInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }
}
 