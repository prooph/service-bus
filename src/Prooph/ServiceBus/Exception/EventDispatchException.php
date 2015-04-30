<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 22:02
 */

namespace Prooph\ServiceBus\Exception;

use Prooph\ServiceBus\Process\EventDispatch;

class EventDispatchException extends RuntimeException
{
    /**
     * @var EventDispatch
     */
    protected $eventDispatch;

    /**
     * @param EventDispatch $eventDispatch
     * @param \Exception $previousException
     * @return EventDispatchException
     */
    public static function failed(EventDispatch $eventDispatch, \Exception $previousException = null)
    {
        $ex = new self(
            sprintf(
                "Event dispatch failed during %s phase.%s",
                $eventDispatch->getName(),
                (is_null($previousException))? '' : ' Error: ' . $previousException->getMessage()
            ),
            422,
            $previousException
        );

        $ex->setFailedDispatch($eventDispatch);

        return $ex;
    }

    /**
     * @return EventDispatch
     */
    public function getFailedCommandDispatch()
    {
        return $this->eventDispatch;
    }

    protected function setFailedDispatch(EventDispatch $eventDispatch)
    {
        $this->eventDispatch = $eventDispatch;
    }
}
 