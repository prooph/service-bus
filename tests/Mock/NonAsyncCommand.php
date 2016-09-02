<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
