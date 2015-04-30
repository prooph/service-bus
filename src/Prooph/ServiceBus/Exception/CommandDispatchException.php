<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 14.09.14 - 22:25
 */

namespace Prooph\ServiceBus\Exception;

use Prooph\ServiceBus\Process\CommandDispatch;

/**
 * Class CommandDispatchException
 *
 * @package Prooph\ServiceBus\Exception
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandDispatchException extends RuntimeException
{
    /**
     * @var CommandDispatch
     */
    protected $commandDispatch;

    /**
     * @param CommandDispatch $commandDispatch
     * @param \Exception $previousException
     * @return CommandDispatchException
     */
    public static function failed(CommandDispatch $commandDispatch, \Exception $previousException = null)
    {
        $ex = new self(
            sprintf(
                "Command dispatch failed during %s phase.%s",
                $commandDispatch->getName(),
                (is_null($previousException))? '' : ' Error: ' . $previousException->getMessage()
            ),
            422,
            $previousException
        );

        $ex->setFailedDispatch($commandDispatch);

        return $ex;
    }

    /**
     * @return CommandDispatch
     */
    public function getFailedCommandDispatch()
    {
        return $this->commandDispatch;
    }

    protected function setFailedDispatch(CommandDispatch $commandDispatch)
    {
        $this->commandDispatch = $commandDispatch;
    }
}
 