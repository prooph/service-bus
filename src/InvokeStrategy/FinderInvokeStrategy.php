<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 5/23/15 - 4:48 PM
 */
namespace Prooph\ServiceBus\InvokeStrategy;

use Prooph\Common\Event\ActionEventDispatcher;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\Common\Messaging\HasMessageName;
use Prooph\ServiceBus\Process\QueryDispatch;

/**
 * Class FinderInvokeStrategy
 *
 * This is a special invoke strategy for finders handling a query message and providing a response by resolving the
 * deferred of the query dispatch.
 *
 * The invoke strategy can handle callable finders and finders which have a method named like the short name of the query.
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FinderInvokeStrategy implements ActionEventListenerAggregate
{
    use DetachAggregateHandlers;

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

        if (is_object($finder)) {
            $queryName = $this->determineQueryName($queryDispatch->getQuery());

            if (method_exists($finder, $queryName)) {
                $finder->{$queryName}($queryDispatch->getQuery(), $queryDispatch->getDeferred());
                return;
            }
        }

        if (is_callable($finder)) {
            $finder($queryDispatch->getQuery(), $queryDispatch->getDeferred());
        }
    }

    /**
     * @param mixed $query
     * @return string
     */
    protected function determineQueryName($query)
    {
        $queryName = ($query instanceof HasMessageName)? $query->messageName() : get_class($query);
        return join('', array_slice(explode('\\', $queryName), -1));
    }
}