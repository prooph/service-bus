<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:55
 */

namespace Prooph\ServiceBus\Command;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageInterface;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

/**
 * Class CommandFactory
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandFactory implements CommandFactoryInterface
{
    /**
     * @var EventManager
     */
    protected $lifeCycleEvents;

    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return mixed a command
     */
    public function fromMessage(MessageInterface $aMessage)
    {
        $result = $this->getLifeCycleEvents()->triggerUntil(
            __FUNCTION__,
            $this,
            array('message' => $aMessage),
            function ($res) {
                return !empty($res);
            }
        );

        if (! $result->stopped()) {
            throw new RuntimeException(
                sprintf(
                    "Command %s can not be build from Message. No appropriate CommandFactory registered",
                    $aMessage->name()
                )

            );
        }

        return $result->last();
    }

    public function setEventManager(EventManager $eventManager)
    {
        $eventManager->addIdentifiers(array(
            'ProophCommandFactory',
            get_class($this)
        ));

        $eventManager->attach('fromMessage', function(Event $e) {

            $message = $e->getParam('message');

            $commandClass = $message->name();

            if (!class_exists($commandClass)) {
                throw new RuntimeException(
                    sprintf(
                        "Class for %s command can not be found",
                        $commandClass
                    )
                );
            }

            $commandRef = new \ReflectionClass($commandClass);

            if ($commandClass !== 'Prooph\ServiceBus\Command\AbstractCommand'
                && ! $commandRef->isSubclassOf('Prooph\ServiceBus\Command\AbstractCommand')) {
                return null;
            }

            return new $commandClass(
                $message->payload(),
                $message->header()->version(),
                $message->header()->uuid(),
                $message->header()->createdOn()
            );
        }, -100);

        $this->lifeCycleEvents = $eventManager;
    }

    /**
     * @return EventManager
     */
    public function getLifeCycleEvents()
    {
        if (is_null($this->lifeCycleEvents)) {
            $this->setEventManager(new EventManager());
        }

        return $this->lifeCycleEvents;
    }
}
