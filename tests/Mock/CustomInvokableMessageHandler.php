<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2021 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Mock;

class CustomInvokableMessageHandler
{
    private $lastMessage;

    private $invokeCounter = 0;

    public function __invoke($message): void
    {
        $this->lastMessage = $message;
        $this->invokeCounter++;
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    public function getInvokeCounter(): int
    {
        return $this->invokeCounter;
    }
}
