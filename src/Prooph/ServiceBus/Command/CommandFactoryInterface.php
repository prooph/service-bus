<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:56
 */

namespace Prooph\ServiceBus\Command;
use Prooph\ServiceBus\Message\MessageInterface;

/**
 * Interface CommandFactoryInterface
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface CommandFactoryInterface
{
    /**
     * @param MessageInterface $aMessage
     * @return CommandInterface
     */
    public function fromMessage(MessageInterface $aMessage);
}
