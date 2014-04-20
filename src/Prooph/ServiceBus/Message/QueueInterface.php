<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:33
 */

namespace Prooph\ServiceBus\Message;

/**
 * Interface QueueInterface
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface QueueInterface
{
    /**
     * @return string
     */
    public function name();
}
