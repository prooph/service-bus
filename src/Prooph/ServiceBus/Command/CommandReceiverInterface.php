<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 19:21
 */

namespace Prooph\ServiceBus\Command;

use Prooph\ServiceBus\Message\MessageInterface;

/**
 * Interface CommandReceiverInterface
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface CommandReceiverInterface
{
    /**
     * @param MessageInterface $aMessage
     * @return void
     */
    public function handle(MessageInterface $aMessage);
}
 