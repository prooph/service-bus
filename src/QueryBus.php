<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;
use React\Promise\Deferred;
use React\Promise\Promise;

/**
 * Class QueryBus
 *
 * The query bus dispatches a query message to a finder.
 * The query is maybe dispatched async so the bus returns a promise
 * which gets either resolved with the response of the finder or rejected with an exception.
 * Additionally the finder can provide an update status but this is not guaranteed.
 *
 * @package Prooph\ServiceBus
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class QueryBus extends MessageBus
{
    const EVENT_INVOKE_FINDER      = 'invoke-finder';

    const EVENT_PARAM_PROMISE = 'query-promise';
    const EVENT_PARAM_DEFERRED = 'query-deferred';

    /**
     * Inject an ActionEventDispatcher instance
     *
     * @param  ActionEventEmitter $actionEventDispatcher
     * @return void
     */
    public function setActionEventEmitter(ActionEventEmitter $actionEventDispatcher)
    {
        $actionEventDispatcher->attachListener(self::EVENT_INVOKE_FINDER, function (ActionEvent $actionEvent) {
            $finder = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);

            if (is_callable($finder)) {
                $query  = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                $deferred = $actionEvent->getParam(self::EVENT_PARAM_DEFERRED);
                $finder($query, $deferred);
                $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, true);
            }
        });

        $this->events = $actionEventDispatcher;
    }

    /**
     * @param mixed $query
     * @return Promise
     */
    public function dispatch($query)
    {
        $deferred = new Deferred();

        $promise = $deferred->promise();

        $actionEvent = $this->getActionEventEmitter()->getNewActionEvent();

        $actionEvent->setTarget($this);

        $actionEvent->setParam(self::EVENT_PARAM_DEFERRED, $deferred);
        $actionEvent->setParam(self::EVENT_PARAM_PROMISE, $promise);

        try {
            $this->initialize($query, $actionEvent);

            if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER) === null) {
                $actionEvent->setName(self::EVENT_ROUTE);
                $this->trigger($actionEvent);
            }

            if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER) === null) {
                throw new RuntimeException(sprintf(
                    "QueryBus was not able to identify a Finder for query %s",
                    $this->getMessageName($query)
                ));
            }

            $finder = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);

            if (is_string($finder) && !is_callable($finder)) {
                $actionEvent->setName(self::EVENT_LOCATE_HANDLER);

                $this->trigger($actionEvent);
            }

            $actionEvent->setName(self::EVENT_INVOKE_FINDER);
            $this->trigger($actionEvent);

            if (! $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLED)) {
                throw new RuntimeException(sprintf('Query %s was not handled', $this->getMessageName($query)));
            }

            $this->triggerFinalize($actionEvent);
        } catch (\Exception $ex) {
            $failedPhase = $actionEvent->getName();

            $actionEvent->setParam(self::EVENT_PARAM_EXCEPTION, $ex);

            $this->triggerError($actionEvent);
            $this->triggerFinalize($actionEvent);

            //Check if a listener has removed the exception to indicate that it was able to handle it
            if ($ex = $actionEvent->getParam(self::EVENT_PARAM_EXCEPTION)) {
                $actionEvent->setName($failedPhase);
                $deferred->reject(MessageDispatchException::failed($actionEvent, $ex));
            }
        }

        return $actionEvent->getParam(self::EVENT_PARAM_PROMISE);
    }
}
