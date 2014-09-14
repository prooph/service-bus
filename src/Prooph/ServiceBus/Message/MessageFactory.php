<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:43
 */

namespace Prooph\ServiceBus\Message;

use Prooph\ServiceBus\Command;
use Prooph\ServiceBus\Event\AbstractEvent;
use Prooph\ServiceBus\Exception\RuntimeException;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

/**
 * Class MessageFactory
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class MessageFactory implements MessageFactoryInterface
{
    /**
     * @var EventManager
     */
    protected $lifeCycleEvents;

    /**
     * @param mixed $aCommand
     * @param string $aSenderName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return MessageInterface
     */
    public function fromCommand($aCommand, $aSenderName)
    {
        if (empty($aCommand)) {
            throw new RuntimeException(
                sprintf(
                    "Can not build message. Empty command received from Sender %s",
                    $aSenderName
                )
            );
        }

        $result = $this->getLifeCycleEvents()->triggerUntil(
            __FUNCTION__,
            $this,
            array('command' => $aCommand, 'sender' => $aSenderName),
            function ($res) {
                return $res instanceof MessageInterface;
            }
        );



        if (!$result->stopped() || ! $result->last() instanceof MessageInterface) {
            throw new RuntimeException(
                sprintf(
                    'Building message from Command %s was not possible. No appropriate message factory registered',
                    (is_object($aCommand)) ? get_class($aCommand) : gettype($aCommand)
                )
            );
        }

        return $result->last();
    }

    /**
     * @param mixed $anEvent
     * @param string $aSenderName
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return MessageInterface
     */
    public function fromEvent($anEvent, $aSenderName)
    {
        if (empty($anEvent)) {
            throw new RuntimeException(
                sprintf(
                    "Can not build message. Empty event received from Sender %s",
                    $aSenderName
                )
            );
        }

        $result = $this->getLifeCycleEvents()->triggerUntil(
            __FUNCTION__,
            $this,
            array('event' => $anEvent, 'sender' => $aSenderName),
            function ($res) {
                return $res instanceof MessageInterface;
            }
        );

        if (!$result->stopped() || ! $result->last() instanceof MessageInterface) {
            throw new RuntimeException(
                sprintf(
                    'Building message from Event %s was not possible. No appropriate message factory registered',
                    (is_object($anEvent)) ? get_class($anEvent) : gettype($anEvent)
                )
            );
        }

        return $result->last();
    }

    public function setEventManager(EventManager $eventManager)
    {
        $eventManager->addIdentifiers(array(
            'ProophMessageFactory',
            get_class($this)
        ));

        $eventManager->attach('fromCommand', function(Event $e) {

            $command = $e->getParam('command');
            $sender  = $e->getParam('sender');

            if ($command instanceof Command) {
                $messageHeader = new MessageHeader(
                    $command->uuid(),
                    $command->createdOn(),
                    $command->version(),
                    $sender,
                    MessageHeader::TYPE_COMMAND
                );

                return new StandardMessage(get_class($command), $messageHeader, $command->payload());
            }
        }, -100);

        $eventManager->attach('fromEvent', function (Event $e) {
            $event = $e->getParam('event');
            $sender = $e->getParam('sender');

            if ($event instanceof AbstractEvent) {
                $messageHeader = new MessageHeader(
                    $event->uuid(),
                    $event->occurredOn(),
                    $event->version(),
                    $sender,
                    MessageHeader::TYPE_EVENT
                );

                return new StandardMessage(get_class($event), $messageHeader, $event->payload());
            }
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
