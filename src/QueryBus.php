<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus;

use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ActionEventEmitter;
use Prooph\ServiceBus\Exception\MessageDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

/**
 * The query bus dispatches a query message to a finder.
 * The query is maybe dispatched async so the bus returns a promise
 * which gets either resolved with the response of the finder or rejected with an exception.
 * Additionally the finder can provide an update status but this is not guaranteed.
 */
class QueryBus extends MessageBus
{
    public const EVENT_PARAM_PROMISE = 'query-promise';
    public const EVENT_PARAM_DEFERRED = 'query-deferred';

    public function __construct(ActionEventEmitter $actionEventEmitter = null)
    {
        parent::__construct($actionEventEmitter);

        $this->events->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                $finder = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER);

                if (is_callable($finder)) {
                    $query = $actionEvent->getParam(self::EVENT_PARAM_MESSAGE);
                    $deferred = $actionEvent->getParam(self::EVENT_PARAM_DEFERRED);
                    $finder($query, $deferred);
                    $actionEvent->setParam(self::EVENT_PARAM_MESSAGE_HANDLED, true);
                }
            },
            self::PRIORITY_INVOKE_HANDLER
        );

        $this->events->attachListener(
            self::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                if ($actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLER) === null) {
                    throw new RuntimeException(sprintf(
                        'QueryBus was not able to identify a Finder for query %s',
                        $this->getMessageName($actionEvent->getParam(self::EVENT_PARAM_MESSAGE))
                    ));
                }
            },
            self::PRIORITY_LOCATE_HANDLER
        );

        $this->events->attachListener(
            self::EVENT_FINALIZE,
            function (ActionEvent $actionEvent): void {
                if ($exception = $actionEvent->getParam(self::EVENT_PARAM_EXCEPTION)) {
                    $deferred = $actionEvent->getParam(self::EVENT_PARAM_DEFERRED);
                    $deferred->reject(MessageDispatchException::failed($exception));
                    $actionEvent->setParam(self::EVENT_PARAM_EXCEPTION, null);
                }
            },
            self::PRIORITY_PROMISE_REJECT
        );
    }

    /**
     * @param mixed $query
     *
     * @throws RuntimeException
     */
    public function dispatch($query): PromiseInterface
    {
        $deferred = new Deferred();

        $actionEventEmitter = $this->events;

        $actionEvent = $actionEventEmitter->getNewActionEvent(
            self::EVENT_DISPATCH,
            $this,
            [
                self::EVENT_PARAM_MESSAGE => $query,
                self::EVENT_PARAM_DEFERRED => $deferred,
                self::EVENT_PARAM_PROMISE => $deferred->promise(),
            ]
        );

        try {
            $actionEventEmitter->dispatch($actionEvent);

            if (! $actionEvent->getParam(self::EVENT_PARAM_MESSAGE_HANDLED)) {
                throw new RuntimeException(sprintf('Query %s was not handled', $this->getMessageName($query)));
            }
        } catch (\Throwable $exception) {
            $actionEvent->setParam(self::EVENT_PARAM_EXCEPTION, $exception);
            //$actionEvent->stopPropagation(false);
        } finally {
            $this->triggerFinalize($actionEvent);
        }

        return $actionEvent->getParam(self::EVENT_PARAM_PROMISE);
    }
}
