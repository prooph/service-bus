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
namespace Prooph\ServiceBus\Exception;


use Prooph\ServiceBus\Process\QueryDispatch;

final class QueryDispatchException extends RuntimeException
{
    /**
     * @var QueryDispatch
     */
    protected $queryDispatch;

    /**
     * @param QueryDispatch $queryDispatch
     * @param \Exception $previousException
     * @return CommandDispatchException
     */
    public static function failed(QueryDispatch $queryDispatch, \Exception $previousException = null)
    {
        $ex = new self(
            sprintf(
                "Query dispatch failed during %s phase.%s",
                $queryDispatch->getName(),
                (is_null($previousException))? '' : ' Error: ' . $previousException->getMessage()
            ),
            422,
            $previousException
        );

        $ex->setFailedDispatch($queryDispatch);

        return $ex;
    }

    /**
     * @return QueryDispatch
     */
    public function getFailedQueryDispatch()
    {
        return $this->queryDispatch;
    }

    protected function setFailedDispatch(QueryDispatch $queryDispatch)
    {
        $this->queryDispatch = $queryDispatch;
    }
} 