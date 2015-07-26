<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:43
 */

namespace Prooph\ServiceBus\Message;

use Prooph\Common\Messaging\DomainMessage;
use Prooph\Common\Messaging\RemoteMessage;
use Prooph\ServiceBus\Exception\RuntimeException;

/**
 * Class ProophDomainMessageToRemoteMessageTranslator
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class ProophDomainMessageToRemoteMessageTranslator implements ToRemoteMessageTranslator
{
    /**
     * @param $domainMessage
     * @return bool
     */
    public function canTranslateToRemoteMessage($domainMessage)
    {
        if ($domainMessage instanceof DomainMessage) return true;
        return false;
    }

    /**
     * @param mixed $domainMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return RemoteMessage
     */
    public function translateToRemoteMessage($domainMessage)
    {
        if ($domainMessage instanceof DomainMessage) return $domainMessage->toRemoteMessage();

        throw new RuntimeException(
            sprintf(
                "Can not build remote message. Invalid domain message type %s given",
                is_object($domainMessage)? get_class($domainMessage) : gettype($domainMessage)
            )
        );
    }
}
