<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 22.05.15 - 22:16
 */
namespace Prooph\ServiceBus;

use Prooph\ServiceBus\Exception\QueryDispatchException;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Process\QueryDispatch;
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
    /**
     * @param mixed $message
     * @throws QueryDispatchException
     * @return Promise
     */
    public function dispatch($message)
    {
        $deferred = new Deferred();

        $promise = $deferred->promise();

        $queryDispatch = QueryDispatch::initializeWith($message, $this, $deferred);

        if (! is_null($this->logger)) {
            $queryDispatch->useLogger($this->logger);
        }

        try {
            $this->trigger($queryDispatch);

            if (is_null($queryDispatch->getQueryName())) {
                $queryDispatch->setName(QueryDispatch::DETECT_MESSAGE_NAME);

                $this->trigger($queryDispatch);
            }

            if (is_null($queryDispatch->getFinder())) {
                $queryDispatch->setName(QueryDispatch::ROUTE);

                $this->trigger($queryDispatch);
            }

            if (is_null($queryDispatch->getFinder())) {
                throw new RuntimeException(sprintf(
                    "QueryBus was not able to identify a Finder for query %s",
                    (is_object($message))? get_class($message) : json_encode($message)
                ));
            }

            if (is_string($queryDispatch->getFinder())) {
                $queryDispatch->setName(QueryDispatch::LOCATE_FINDER);

                $this->trigger($queryDispatch);
            }

            $queryDispatch->setName(QueryDispatch::INVOKE_FINDER);

            $this->trigger($queryDispatch);

            $this->triggerFinalize($queryDispatch);
        } catch (\Exception $ex) {
            $failedPhase = $queryDispatch->getName();

            $queryDispatch->setException($ex);
            $this->triggerError($queryDispatch);
            $this->triggerFinalize($queryDispatch);

            //Check if a listener has removed the exception to indicate that it was able to handle it
            if ($ex = $queryDispatch->getException()) {
                $queryDispatch->setName($failedPhase);
                $queryDispatch->getDeferred()->reject(QueryDispatchException::failed($queryDispatch, $ex));
            }
        }

        return $promise;
    }
}