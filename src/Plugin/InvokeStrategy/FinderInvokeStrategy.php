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
namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\Common\Event\ActionEventListenerAggregate;
use Prooph\Common\Event\DetachAggregateHandlers;
use Prooph\Common\Messaging\HasMessageName;
use Prooph\ServiceBus\QueryBus;

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
     * @param ActionEventEmitter $dispatcher
     */
    public function attach(ActionEventEmitter $dispatcher)
    {
        $this->trackHandler($dispatcher->attachListener(QueryBus::EVENT_INVOKE_FINDER, $this));
    }

    /**
     * @param ActionEvent $actionEvent
     */
    public function __invoke(ActionEvent $actionEvent)
    {
        $finder = $actionEvent->getParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLER);

        $query = $actionEvent->getParam(QueryBus::EVENT_PARAM_MESSAGE);

        $deferred = $actionEvent->getParam(QueryBus::EVENT_PARAM_DEFERRED);

        if (is_object($finder)) {
            $queryName = $this->determineQueryName($query);

            if (method_exists($finder, $queryName)) {
                $finder->{$queryName}($query, $deferred);
                return;
            }
        }
    }

    /**
     * @param mixed $query
     * @return string
     */
    private function determineQueryName($query)
    {
        $queryName = ($query instanceof HasMessageName)? $query->messageName() : is_object($query)? get_class($query) : gettype($query);
        return join('', array_slice(explode('\\', $queryName), -1));
    }
}