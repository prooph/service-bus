<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/02/15 - 8:38 PM
 */

namespace Prooph\ServiceBusTest\Mock;

/**
 * Class CustomMessageCommandHandler
 * @package Prooph\ServiceBusTest\Mock
 */
final class CustomMessageCommandHandler
{
    private $lastMessage;

    public function handleCustomMessage($message)
    {
        $this->lastMessage = $message;
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }
}
