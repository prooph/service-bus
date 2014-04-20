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

use Prooph\ServiceBus\Command\CommandInterface;
use Prooph\ServiceBus\Event\EventInterface;

/**
 * Class MessageFactory
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class MessageFactory implements MessageFactoryInterface
{
    /**
     * @param CommandInterface $aCommand
     * @param string           $aSenderName
     * @return MessageInterface
     */
    public function fromCommand(CommandInterface $aCommand, $aSenderName)
    {
        $messageHeader = new MessageHeader(
            $aCommand->uuid(),
            $aCommand->createdOn(),
            $aCommand->version(),
            $aSenderName,
            MessageHeader::TYPE_COMMAND
        );

        return new StandardMessage(get_class($aCommand), $messageHeader, $aCommand->payload());
    }

    /**
     * @param EventInterface $anEvent
     * @param string         $aSenderName
     * @return MessageInterface
     */
    public function fromEvent(EventInterface $anEvent, $aSenderName)
    {
        $messageHeader = new MessageHeader(
            $anEvent->uuid(),
            $anEvent->occurredOn(),
            $anEvent->version(),
            $aSenderName,
            MessageHeader::TYPE_EVENT
        );

        return new StandardMessage(get_class($anEvent), $messageHeader, $anEvent->payload());
    }
}
