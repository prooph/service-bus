<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2013-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;
use Prooph\ServiceBus\Async\AsyncMessage;

class AsyncCommand extends Command implements PayloadConstructable, AsyncMessage
{
    use PayloadTrait;
    /**
     * @param string $data
     * @return AsyncCommand
     */
    public static function createCommand($data)
    {
        return new self([
            'data' => $data
        ]);
    }
}
