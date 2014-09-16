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
use Prooph\ServiceBus\Event;
use Prooph\ServiceBus\Exception\RuntimeException;
use Zend\EventManager\EventManager;

/**
 * Class MessageTranslator
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class MessageTranslator implements MessageTranslatorInterface
{
    /**
     * @param $aCommandOrEvent
     * @return bool
     */
    public function canTranslateToMessage($aCommandOrEvent)
    {
        if ($aCommandOrEvent instanceof Command) return true;
        if ($aCommandOrEvent instanceof Event) return true;
        return false;
    }

    /**
     * @param mixed $aCommand
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return MessageInterface
     */
    public function fromCommandToMessage($aCommand)
    {
        if (! $aCommand instanceof Command) {
            throw new RuntimeException(
                sprintf(
                    "Can not build message. Provided command must be of type Prooph\ServiceBus\Command but type of %s given",
                    is_object($aCommand)? get_class($aCommand) : gettype($aCommand)
                )
            );
        }

        $messageHeader = new MessageHeader(
            $aCommand->uuid(),
            $aCommand->createdOn(),
            $aCommand->version(),
            MessageHeader::TYPE_COMMAND
        );

        return new StandardMessage($aCommand->getMessageName(), $messageHeader, $aCommand->payload());

    }

    /**
     * @param mixed $anEvent
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return MessageInterface
     */
    public function fromEventToMessage($anEvent)
    {
        if (! $anEvent instanceof Event) {
            throw new RuntimeException(
                sprintf(
                    "Can not build message. Provided event must be of type Prooph\ServiceBus\Event but type of %s given",
                    is_object($anEvent)? get_class($anEvent) : gettype($anEvent)
                )
            );
        }

        $messageHeader = new MessageHeader(
            $anEvent->uuid(),
            $anEvent->occurredOn(),
            $anEvent->version(),
            MessageHeader::TYPE_EVENT
        );

        return new StandardMessage($anEvent->getMessageName(), $messageHeader, $anEvent->payload());
    }

    /**
     * @param MessageInterface $aMessage
     * @return mixed
     */
    public function fromMessageToCommandOrEvent(MessageInterface $aMessage)
    {
        if ($aMessage->header()->type() === MessageHeader::TYPE_COMMAND) {
            return $this->fromMessageToCommand($aMessage);
        } else {
            return $this->fromMessageToEvent($aMessage);
        }
    }

    /**
     * @param MessageInterface $aMessage
     * @return \Prooph\ServiceBus\Command
     */
    protected function fromMessageToCommand(MessageInterface $aMessage)
    {
        return new Command(
            $aMessage->name(),
            $aMessage->payload(),
            $aMessage->header()->version(),
            $aMessage->header()->uuid(),
            $aMessage->header()->createdOn()
        );
    }

    /**
     * @param MessageInterface $aMessage
     * @return Event
     */
    protected function fromMessageToEvent(MessageInterface $aMessage)
    {
        return new Event(
            $aMessage->name(),
            $aMessage->payload(),
            $aMessage->header()->version(),
            $aMessage->header()->uuid(),
            $aMessage->header()->createdOn()
        );
    }
}
