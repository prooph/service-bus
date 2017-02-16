<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2013-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProophTest\ServiceBus\Mock;

/**
 * Class CustomMessageCommandHandler
 * @package ProophTest\ServiceBus\Mock
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
