<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/30/16 - 8:35 PM
 */
declare(strict_types=1);

namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\Async\MessageProducer;
use React\Promise\Deferred;

class NoopMessageProducer implements MessageProducer
{
    private $invoked = false;

    public function __invoke(Message $message, Deferred $deferred = null)
    {
        $this->invoked = true;
    }

    public function isInvoked()
    {
        return $this->invoked;
    }
}
