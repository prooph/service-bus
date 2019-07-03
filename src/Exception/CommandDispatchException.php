<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2019 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Exception;

class CommandDispatchException extends MessageDispatchException
{
    /**
     * @var array
     */
    private $pendingCommands = [];

    public static function wrap(\Throwable $dispatchException, array $pendingCommands): CommandDispatchException
    {
        if ($dispatchException instanceof MessageDispatchException) {
            $ex = parent::failed($dispatchException->getPrevious());

            $ex->pendingCommands = $pendingCommands;

            return $ex;
        }

        $ex = new static('Command dispatch failed. See previous exception for details.', 422, $dispatchException);

        $ex->pendingCommands = $pendingCommands;

        return $ex;
    }

    public function getPendingCommands(): array
    {
        return $this->pendingCommands;
    }
}
