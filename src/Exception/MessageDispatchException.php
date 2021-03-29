<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2021 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Exception;

use Prooph\Common\Event\ActionEvent;

class MessageDispatchException extends RuntimeException
{
    /**
     * @var ActionEvent
     */
    protected $actionEvent;

    public static function failed(\Throwable $dispatchException): MessageDispatchException
    {
        return new static('Message dispatch failed. See previous exception for details.', 422, $dispatchException);
    }
}
