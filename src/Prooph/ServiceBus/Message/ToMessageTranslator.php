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

/**
 * Class ToMessageTranslator
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class ToMessageTranslator implements ToMessageTranslatorInterface
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
     * @param mixed $aCommandOrEvent
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return MessageInterface
     */
    public function translateToMessage($aCommandOrEvent)
    {
        if ($aCommandOrEvent instanceof Command) return $this->fromCommandToMessage($aCommandOrEvent);
        if ($aCommandOrEvent instanceof Event)   return $this->fromEventToMessage($aCommandOrEvent);

        throw new RuntimeException(
            sprintf(
                "Can not build message. Invalid command or event type %s given",
                is_object($aCommandOrEvent)? get_class($aCommandOrEvent) : gettype($aCommandOrEvent)
            )
        );
    }

    /**
     * @param mixed $aCommand
     * @return MessageInterface
     */
    protected function fromCommandToMessage($aCommand)
    {
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
     * @return MessageInterface
     */
    protected function fromEventToMessage($anEvent)
    {
        $messageHeader = new MessageHeader(
            $anEvent->uuid(),
            $anEvent->occurredOn(),
            $anEvent->version(),
            MessageHeader::TYPE_EVENT
        );

        return new StandardMessage($anEvent->getMessageName(), $messageHeader, $anEvent->payload());
    }
}
