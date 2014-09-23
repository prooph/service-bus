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

use Prooph\ServiceBus\Command;
use Prooph\ServiceBus\Event;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Process\EventDispatch;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Class FromMessageTranslator
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class FromMessageTranslator extends AbstractListenerAggregate
{
    /**
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $identifiers = $events->getIdentifiers();

        if (in_array('command_bus', $identifiers)) {
            $this->listeners[] = $events->attach(CommandDispatch::INITIALIZE, array($this, 'onInitializeCommandDispatch'), 100);
        }

        if (in_array('event_bus', $identifiers)) {
            $this->listeners[] = $events->attach(EventDispatch::INITIALIZE, array($this, 'onInitializeEventDispatch'), 100);
        }
    }

    public function onInitializeCommandDispatch(CommandDispatch $commandDispatch)
    {
        $message = $commandDispatch->getCommand();

        if ($message instanceof MessageInterface) {
            $command = $this->translateFromMessage($message);

            $commandDispatch->setCommand($command);
        }
    }

    public function onInitializeEventDispatch(EventDispatch $eventDispatch)
    {
        $message = $eventDispatch->getEvent();

        if ($message instanceof MessageInterface) {
            $event = $this->translateFromMessage($message);

            $eventDispatch->setEvent($event);
        }
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
        return new Command(
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
        return new Event(
            $aMessage->name(),
            $aMessage->payload(),
            $aMessage->header()->version(),
            $aMessage->header()->uuid(),
            $aMessage->header()->createdOn()
        );
    }
}
 