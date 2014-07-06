<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 22:30
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Command\AbstractCommandFactoryFactory;
use Prooph\ServiceBus\Command\CommandFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception;

/**
 * Class CommandFactoryLoader
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandFactoryLoader extends AbstractPluginManager
{
    /**
     * @param ConfigInterface $aConfig
     */
    public function __construct(ConfigInterface $aConfig = null)
    {
        parent::__construct($aConfig);

        $this->abstractFactories[] = new AbstractCommandFactoryFactory();
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
        if (! $plugin instanceof CommandFactoryInterface) {
            throw new Exception\RuntimeException(sprintf(
                'CommandFactory must be instance of Prooph\ServiceBus\Command\CommandFactoryInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }

    /**
     * @param string $aMessageName
     * @return CommandFactoryInterface
     */
    public function getCommandFactoryFor($aMessageName)
    {
        return $this->get($aMessageName);
    }
}
 