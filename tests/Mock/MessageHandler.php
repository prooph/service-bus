<?php

/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Mock;

class MessageHandler
{
    private $lastMessage;

    private $invokeCounter = 0;

    public function __invoke($message): void
    {
        $this->lastMessage = $message;
        $this->invokeCounter++;
    }

    public function handle($message): void
    {
        $this->lastMessage = $message;
        $this->invokeCounter++;
    }

    public function onEvent($message): void
    {
        $this->lastMessage = $message;
        $this->invokeCounter++;
    }

    public function getInvokeCounter(): int
    {
        return $this->invokeCounter;
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }
}
