<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/30/16 - 8:35 PM
 */
namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Messaging\DomainEvent;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;
use Prooph\ServiceBus\Async\AsyncMessage;

final class AsyncEvent extends DomainEvent implements PayloadConstructable, AsyncMessage
{
    use PayloadTrait;

    public static function createEvent($data)
    {
        return new self(['data' => $data]);
    }
}
