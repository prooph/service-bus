<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:07
 */

namespace Prooph\ServiceBus\Message;

/**
 * Interface MessageTranslatorInterface
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface MessageTranslatorInterface
{
    /**
     * @param $aCommandOrEvent
     * @return bool
     */
    public function canTranslateToMessage($aCommandOrEvent);

    /**
     * @param mixed $aCommand
     * @return MessageInterface
     */
    public function fromCommandToMessage($aCommand);

    /**
     * @param mixed $anEvent
     * @return MessageInterface
     */
    public function fromEventToMessage($anEvent);

    /**
     * @param MessageInterface $aMessage
     * @return mixed
     */
    public function fromMessageToCommandOrEvent(MessageInterface $aMessage);
}
 