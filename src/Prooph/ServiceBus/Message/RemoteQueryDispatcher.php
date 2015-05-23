<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 5/23/15 - 6:58 PM
 */

namespace Prooph\ServiceBus\Message;
use Prooph\Common\Messaging\RemoteMessage;
use React\Promise\Deferred;

/**
 * Interface RemoteQueryDispatcher
 *
 * A remote query dispatcher is capable of dispatching a query message to a remote system
 * and resolve the given deferred with the answer of the remote system.
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface RemoteQueryDispatcher 
{
    /**
     * @param RemoteMessage $queryMessage
     * @param Deferred $deferred
     * @return void
     */
    public function dispatchQuery(RemoteMessage $queryMessage, Deferred $deferred);
} 