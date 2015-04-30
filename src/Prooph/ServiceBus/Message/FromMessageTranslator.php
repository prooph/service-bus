<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
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
use Prooph\ServiceBus\Process\MessageDispatch;

/**
 * Class FromMessageTranslator
 *
 * If incoming message is of type Prooph\ServiceBus\Message\MessageInterface
 * it is translated to Prooph\ServiceBus\Command|mixed or Prooph\ServiceBus\Event|mixed
 * depending on the Prooph\ServiceBus\Message\MessageHeader::TYPE_* and if the message name is an existing class
 * 
 * @see FromMessageTranslator::fromMessageToCommand
 * @see FromMessageTranslator::fromMessageToEvent
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class FromMessageTranslator implements ActionEventListenerAggregate
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

        if ($message instanceof MessageInterface) {
            $message = $this->translateFromMessage($message);

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
     * @param MessageInterface $aMessage
     * @return mixed
     */
    public function translateFromMessage(MessageInterface $aMessage)
    {
        $defaultCommandOrEventClass = ($aMessage->header()->type() === MessageHeader::TYPE_COMMAND)?
            'Prooph\ServiceBus\Command' : 'Prooph\ServiceBus\Event';

        $messageClass = (class_exists($aMessage->name()))? $aMessage->name() : $defaultCommandOrEventClass;

        return new $messageClass(
            $aMessage->name(),
            $aMessage->payload(),
            $aMessage->header()->version(),
            $aMessage->header()->uuid(),
            $aMessage->header()->createdOn()
        );
    }
}
 