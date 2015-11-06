<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 11/06/15 - 3:00 PM
 */

namespace ProophTest\ServiceBus\Mock;

use Prooph\ServiceBus\MessageBus;

/**
 * Class CustomMessageBus
 * @package ProophTest\ServiceBus\Mock
 */
final class CustomMessageBus extends MessageBus
{
    /**
     * @param mixed $message
     */
    public function dispatch($message)
    {
        // do nothing
    }
}
