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

class Finder
{
    private $message;

    private $deferred;

    public function find($message, $deferred)
    {
        $this->message = $message;
        $this->deferred = $deferred;
    }

    public function getLastMessage()
    {
        return $this->message;
    }

    public function getLastDeferred()
    {
        return $this->deferred;
    }
}
