<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 23:10
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Message\AbstractMessageFactoryFactory;
use Prooph\ServiceBus\Message\MessageFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception;

/**
 * Class MessageFactoryLoader
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class MessageFactoryLoader extends AbstractPluginManager
{

    /**
     * @param ConfigInterface $aConfig
     */
    public function __construct(ConfigInterface $aConfig = null)
    {
        parent::__construct($aConfig);

        $this->abstractFactories[] = new AbstractMessageFactoryFactory();
    }

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @throws Exception\RuntimeException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        if (! $plugin instanceof MessageFactoryInterface) {
            throw new Exception\RuntimeException(sprintf(
                'MessageFactory must be instance of Prooph\ServiceBus\Message\MessageFactoryInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }

    /**
     * @param string $aMessageName
     * @return MessageFactoryInterface
     */
    public function getMessageFactoryFor($aMessageName)
    {
        return $this->get($aMessageName);
    }
}
 