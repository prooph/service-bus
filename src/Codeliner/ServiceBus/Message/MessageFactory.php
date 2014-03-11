<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:43
 */

namespace Codeliner\ServiceBus\Message;

use Codeliner\ServiceBus\Command\CommandInterface;
use Codeliner\ServiceBus\Event\EventInterface;

/**
 * Class MessageFactory
 *
 * @package Codeliner\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
            $aSenderName
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
            $anEvent->createdOn(),
            $anEvent->version(),
            $aSenderName
        );

        return new StandardMessage(get_class($anEvent), $messageHeader, $anEvent->payload());
    }
}
