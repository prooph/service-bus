<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 22:51
 */

namespace Prooph\ServiceBus\InvokeStrategy;

use Prooph\ServiceBus\Message\MessageDispatcherInterface;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Message\MessageTranslator;
use Prooph\ServiceBus\Message\MessageTranslatorInterface;

/**
 * Class ForwardToMessageDispatcherStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ForwardToMessageDispatcherStrategy extends AbstractInvokeStrategy
{
    /**
     * @var MessageTranslatorInterface
     */
    protected $messageTranslator;

    /**
     * @param MessageTranslatorInterface $messageTranslator
     */
    public function __construct(
        MessageTranslatorInterface $messageTranslator = null
    )
    {
        $this->messageTranslator = $messageTranslator;
    }

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    protected function canInvoke($aHandler, $aCommandOrEvent)
    {
        if ($aHandler instanceof MessageDispatcherInterface) {
            if ($aCommandOrEvent instanceof MessageInterface
                || $this->getMessageTranslator()->canTranslateToMessage($aCommandOrEvent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     */
    protected function invoke($aHandler, $aCommandOrEvent)
    {
        $message = $aCommandOrEvent;

        if (! $message instanceof MessageInterface) {
            $message = $this->getMessageTranslator()->translateToMessage($aCommandOrEvent);
        }

        $aHandler->dispatch($message);
    }

    /**
     * @return MessageTranslatorInterface
     */
    protected function getMessageTranslator()
    {
        if (is_null($this->messageTranslator)) {
            $this->messageTranslator = new MessageTranslator();
        }

        return $this->getMessageTranslator;
    }
}
 