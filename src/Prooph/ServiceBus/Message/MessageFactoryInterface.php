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

use Prooph\ServiceBus\Command\CommandInterface;
use Prooph\ServiceBus\Event\EventInterface;

/**
 * Interface MessageFactoryInterface
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface MessageFactoryInterface 
{
    /**
     * @param CommandInterface $aCommand
     * @param string           $aSenderName
     * @return MessageInterface
     */
    public function fromCommand(CommandInterface $aCommand, $aSenderName);

    /**
     * @param EventInterface $anEvent
     * @param string         $aSenderName
     * @return MessageInterface
     */
    public function fromEvent(EventInterface $anEvent, $aSenderName);
}
 