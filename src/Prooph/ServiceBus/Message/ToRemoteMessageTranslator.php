<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:07
 */

namespace Prooph\ServiceBus\Message;

use Prooph\Common\Messaging\RemoteMessage;

/**
 * Interface ToRemoteMessageTranslator
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface ToRemoteMessageTranslator
{
    /**
     * @param $domainMessage
     * @return bool
     */
    public function canTranslateToRemoteMessage($domainMessage);

    /**
     * @param mixed $domainMessage
     * @return RemoteMessage
     */
    public function translateToRemoteMessage($domainMessage);
}
 