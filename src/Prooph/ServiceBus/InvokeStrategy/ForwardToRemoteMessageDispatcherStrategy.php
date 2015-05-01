<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 22:51
 */

namespace Prooph\ServiceBus\InvokeStrategy;

use Prooph\ServiceBus\Message\RemoteMessageDispatcher;
use Prooph\ServiceBus\Message\ProophDomainMessageToRemoteMessageTranslator;

/**
 * Class ForwardToRemoteMessageDispatcherStrategy
 *
 * This invoke strategy comes into play when a domain message should be dispatched to a
 * RemoteMessageDispatcher. The strategy translates the domain message to a Prooph\Common\Messaging\RemoteMessage
 * with the help of a ToRemoteMessageTranslator and forwards the RemoteMessage to the RemoteMessageDispatcher.
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ForwardToRemoteMessageDispatcherStrategy extends AbstractInvokeStrategy
{
    /**
     * @var ToRemoteMessageTranslator
     */
    protected $messageTranslator;

    /**
     * @param ToRemoteMessageTranslator $messageTranslator
     */
    public function __construct(
        ProophDomainMessageToRemoteMessageTranslator $messageTranslator = null
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
        if ($aHandler instanceof RemoteMessageDispatcher) {
            if ($aCommandOrEvent instanceof MessageInterface
                || $this->getMessageTranslator()->canTranslateToRemoteMessage($aCommandOrEvent)) {
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
            $message = $this->getMessageTranslator()->translateToRemoteMessage($aCommandOrEvent);
        }

        $aHandler->dispatch($message);
    }

    /**
     * @return ToRemoteMessageTranslator
     */
    protected function getMessageTranslator()
    {
        if (is_null($this->messageTranslator)) {
            $this->messageTranslator = new ProophDomainMessageToRemoteMessageTranslator();
        }

        return $this->messageTranslator;
    }
}
 