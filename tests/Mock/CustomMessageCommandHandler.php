<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace ProophTest\ServiceBus\Mock;

/**
 * Class CustomMessageCommandHandler
 * @package ProophTest\ServiceBus\Mock
 */
final class CustomMessageCommandHandler
{
    private $lastMessage;

    public function handleCustomMessage($message) : void
    {
        $this->lastMessage = $message;
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }
}
