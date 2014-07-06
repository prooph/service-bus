<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.07.14 - 22:58
 */

namespace Prooph\ServiceBus\Service;

use Prooph\ServiceBus\Event\AbstractEventFactoryFactory;
use Prooph\ServiceBus\Event\EventFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception;

class EventFactoryLoader extends AbstractPluginManager
{
    /**
     * @param ConfigInterface $aConfig
     */
    public function __construct(ConfigInterface $aConfig = null)
    {
        parent::__construct($aConfig);

        $this->abstractFactories[] = new AbstractEventFactoryFactory();
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
        if (! $plugin instanceof EventFactoryInterface) {
            throw new Exception\RuntimeException(sprintf(
                'EventFactory must be instance of Prooph\ServiceBus\Event\EventFactoryInterface,'
                . 'instance of type %s given',
                ((is_object($plugin)? get_class($plugin)  : gettype($plugin)))
            ));
        }
    }

    /**
     * @param string $aMessageName
     * @return EventFactoryInterface
     */
    public function getEventFactoryFor($aMessageName)
    {
        return $this->get($aMessageName);
    }
}
 