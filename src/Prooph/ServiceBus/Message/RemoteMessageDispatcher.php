<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:27
 */

namespace Prooph\ServiceBus\Message;

use Prooph\Common\Messaging\RemoteMessage;

/**
 * Interface RemoteMessageDispatcher
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface RemoteMessageDispatcher
{
    /**
     * @param RemoteMessage $message
     * @return void
     */
    public function dispatch(RemoteMessage $message);
}