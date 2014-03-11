<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:07
 */

namespace Codeliner\ServiceBus\Message;

use Codeliner\ServiceBus\Command\CommandInterface;
use Codeliner\ServiceBus\Event\EventInterface;

/**
 * Interface MessageFactoryInterface
 *
 * @package Codeliner\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
 