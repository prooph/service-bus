<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 21:16
 */

namespace Prooph\ServiceBusTest\Mock;

use Prooph\ServiceBus\Command;

/**
 * Class DoSomething
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DoSomething extends Command
{
    /**
     * @param string $data
     * @return DoSomething
     */
    public static function fromData($data)
    {
        return new static(__CLASS__, array('data' => $data));
    }

    /**
     * @return string
     */
    public function data()
    {
        return $this->payload['data'];
    }
}
 