<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:08
 */

namespace Prooph\ServiceBus\Message;

/**
 * Interface PayloadInterface
 *
 * @package Prooph\ServiceBus\Payload
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface PayloadInterface
{
    /**
     * @return array
     */
    public function getArrayCopy();
} 