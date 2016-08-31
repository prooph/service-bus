<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus\Exception;

/**
 * Class CommandDispatchException
 *
 * @package Prooph\ServiceBus\Exception
 */
class CommandDispatchException extends MessageDispatchException
{
    private $pendingCommands = [];

    /**
     * @param \Exception $dispatchException
     * @param array $pendingCommands
     * @return CommandDispatchException
     */
    public static function wrap(\Exception $dispatchException, array $pendingCommands)
    {
        if ($dispatchException instanceof MessageDispatchException) {
            $ex = parent::failed($dispatchException->getFailedDispatchEvent(), $dispatchException->getPrevious());

            $ex->pendingCommands = $pendingCommands;

            return $ex;
        }

        $ex = new static("Command dispatch failed. See previous exception for details.", 422, $dispatchException);

        $ex->pendingCommands = $pendingCommands;

        return $ex;
    }

    /**
     * @return array
     */
    public function getPendingCommands()
    {
        return $this->pendingCommands;
    }
}
