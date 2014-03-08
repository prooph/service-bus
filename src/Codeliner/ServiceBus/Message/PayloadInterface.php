<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:08
 */

namespace Codeliner\ServiceBus\Message;

/**
 * Interface PayloadInterface
 *
 * @package Codeliner\ServiceBus\Payload
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface PayloadInterface
{
    /**
     * @return array
     */
    public function getArrayCopy();
} 