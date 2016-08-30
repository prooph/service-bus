<?php
/**
 * Created by PhpStorm.
 * User: GuyRadford
 * Date: 28/08/2016
 * Time: 12:07
 */

namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class NonAsyncCommand extends Command implements PayloadConstructable
{
    use PayloadTrait;
    /**
     * @param string $data
     * @return NonAsyncCommand
     */
    public static function createCommand($data)
    {
        return new self([
            'data' => $data
        ]);
    }
}
