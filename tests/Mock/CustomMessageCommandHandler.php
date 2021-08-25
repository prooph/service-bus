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

class CustomMessageCommandHandler
{
    private $lastMessage;

    public function handle($message): void
    {
        $this->lastMessage = $message;
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }
}
