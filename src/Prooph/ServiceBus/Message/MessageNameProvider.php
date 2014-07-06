<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.07.14 - 18:34
 */

namespace Prooph\ServiceBus\Message;

/**
 * Interface MessageNameProvider
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface MessageNameProvider 
{
    /**
     * @return string Name of the message
     */
    public function getMessageName();
}
 