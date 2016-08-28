<?php
/**
 * Created by PhpStorm.
 * User: GuyRadford
 * Date: 28/08/2016
 * Time: 12:06
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
