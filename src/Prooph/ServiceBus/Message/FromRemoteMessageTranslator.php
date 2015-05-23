<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 23.09.14 - 19:23
 */

namespace Prooph\ServiceBus\Message;

use Prooph\Common\Event\ActionEventDispatcher;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\Common\Messaging\MessageHeader;
use Prooph\Common\Messaging\Query;
use Prooph\Common\Messaging\RemoteMessage;
use Prooph\ServiceBus\Process\MessageDispatch;

/**
 * Class FromRemoteMessageTranslator
 *
 * If the incoming message is of type Prooph\Common\Messaging\RemoteMessage
 * it is translated to a Prooph\Common\Messaging\DomainMessage respecting the Prooph\Common\Messaging\MessageHeader::TYPE_*
 * and if RemoteMessage::name can be resolved to an existing class it is used instead of the base classes.
 *
 * Note: A custom command or domain event class MUST implement a static fromRemoteMessage factory method otherwise
 * the translator will break!
 * 
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class FromRemoteMessageTranslator implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * Plugin listens on MessageDispatch::INITIALIZE with priority 100
     *
     * @param MessageDispatch $messageDispatch
     */
    public function __invoke(MessageDispatch $messageDispatch)
    {
        $message = $messageDispatch->getMessage();

        if ($message instanceof RemoteMessage) {
            $message = $this->translateFromRemoteMessage($message);

            $messageDispatch->setMessage($message);
        }
    }

    /**
     * @param ActionEventDispatcher $events
     *
     * @return void
     */
    public function attach(ActionEventDispatcher $events)
    {
        $this->trackHandler($events->attachListener(MessageDispatch::INITIALIZE, $this, 100));
    }

    /**
     * @param RemoteMessage $remoteMessage
     * @return mixed
     */
    public function translateFromRemoteMessage(RemoteMessage $remoteMessage)
    {
        $defaultMessageClass = ($remoteMessage->header()->type() === MessageHeader::TYPE_COMMAND)
            ? Command::class :
            ($remoteMessage->header()->type() === MessageHeader::TYPE_QUERY)
                ? Query::class : DomainEvent::class;

        $messageClass = (class_exists($remoteMessage->name()))? $remoteMessage->name() : $defaultMessageClass;

        return $messageClass::fromRemoteMessage($remoteMessage);
    }
}
 