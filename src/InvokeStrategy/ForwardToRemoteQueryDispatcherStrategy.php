<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 5/23/15 - 7:35 PM
 */
namespace Prooph\ServiceBus\InvokeStrategy;

use Prooph\Common\Event\ActionEventDispatcher;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\Common\Messaging\RemoteMessage;
use Prooph\ServiceBus\Message\ProophDomainMessageToRemoteMessageTranslator;
use Prooph\ServiceBus\Message\RemoteQueryDispatcher;
use Prooph\ServiceBus\Message\ToRemoteMessageTranslator;
use Prooph\ServiceBus\Process\QueryDispatch;

/**
 * Class ForwardToRemoteQueryDispatcherStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ForwardToRemoteQueryDispatcherStrategy implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

    /**
     * @var ToRemoteMessageTranslator
     */
    protected $messageTranslator;

    /**
     * @param ToRemoteMessageTranslator $messageTranslator
     */
    public function __construct(ToRemoteMessageTranslator $messageTranslator = null)
    {
        $this->messageTranslator = $messageTranslator;
    }

    /**
     * @param ActionEventDispatcher $dispatcher
     */
    public function attach(ActionEventDispatcher $dispatcher)
    {
        $this->trackHandler($dispatcher->attachListener(QueryDispatch::INVOKE_FINDER, [$this, 'onInvokeFinder']));
    }

    /**
     * @param QueryDispatch $queryDispatch
     */
    public function onInvokeFinder(QueryDispatch $queryDispatch)
    {
        $finder = $queryDispatch->getFinder();
        $query  = $queryDispatch->getQuery();

        if ($finder instanceof RemoteQueryDispatcher
            && ($query instanceof RemoteMessage || $this->getMessageTranslator()->canTranslateToRemoteMessage($query))) {

            $query = $queryDispatch->getQuery();

            if (! $query instanceof RemoteMessage ) {
                $query = $this->getMessageTranslator()->translateToRemoteMessage($query);
            }

            $finder->dispatchQuery($query, $queryDispatch->getDeferred());
        }
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