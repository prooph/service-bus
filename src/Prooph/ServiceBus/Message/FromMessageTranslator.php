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

use Prooph\ServiceBus\Process\MessageDispatch;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

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
class FromMessageTranslator extends AbstractListenerAggregate
{
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
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(MessageDispatch::INITIALIZE, $this, 100);
    }

    /**
     * @param MessageInterface $aMessage
     * @return mixed
     */
    public function translateFromMessage(MessageInterface $aMessage)
    {
        if ($aMessage->header()->type() === MessageHeader::TYPE_COMMAND) {
            return $this->fromMessageToCommand($aMessage);
        } else {
            return $this->fromMessageToEvent($aMessage);
        }
    }

    /**
     * @param MessageInterface $aMessage
     * @return \Prooph\ServiceBus\Command
     */
    protected function fromMessageToCommand(MessageInterface $aMessage)
    {
        $commandClass = (class_exists($aMessage->name()))? $aMessage->name() : 'Prooph\ServiceBus\Command';

        return new $commandClass(
            $aMessage->name(),
            $aMessage->payload(),
            $aMessage->header()->version(),
            $aMessage->header()->uuid(),
            $aMessage->header()->createdOn()
        );
    }

    /**
     * @param MessageInterface $aMessage
     * @return Event
     */
    protected function fromMessageToEvent(MessageInterface $aMessage)
    {
        $eventClass = (class_exists($aMessage->name()))? $aMessage->name() : 'Prooph\ServiceBus\Event';

        return new $eventClass(
            $aMessage->name(),
            $aMessage->payload(),
            $aMessage->header()->version(),
            $aMessage->header()->uuid(),
            $aMessage->header()->createdOn()
        );
    }
}
 