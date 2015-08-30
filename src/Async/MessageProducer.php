<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 8/30/15 - 10:47 PM
 */
namespace Prooph\ServiceBus\Async;

use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\Exception\RuntimeException;
use React\Promise\Deferred;

/**
 * Interface MessageProducer
 *
 * The message buses treat message producers like every other message handlers.
 * However, this interface marks a handler as an async message producer.
 *
 * @package Prooph\ServiceBus\Async
 */
interface MessageProducer
{
    /**
     * Message producers need to be invokable.
     *
     * A producer MUST be able to handle a message async without returning a response.
     * A producer MAY also support future response by resolving the passed $deferred.
     *
     * Note: A $deferred is only passed by a QueryBus but in this case the $deferred
     *       MUST either be resolved/rejected OR the message producer
     *       MUST throw a Prooph\ServiceBus\Exception\RuntimeException if it cannot
     *       handle the $deferred
     *
     * @param Message $message
     * @param null|Deferred $deferred
     * @throws RuntimeException If a $deferred is passed but producer can not handle it
     * @return void
     */
    public function __invoke(Message $message, Deferred $deferred = null);
}
