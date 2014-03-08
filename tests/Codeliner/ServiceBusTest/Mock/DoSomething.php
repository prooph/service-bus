<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 21:16
 */

namespace Codeliner\ServiceBusTest\Mock;

use Codeliner\ArrayReader\ArrayReader;
use Codeliner\ServiceBus\Command\AbstractCommand;
use Codeliner\ServiceBus\Message\MessageHeader;
use Rhumsaa\Uuid\Uuid;

/**
 * Class DoSomething
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DoSomething extends AbstractCommand
{
    /**
     * @param string $data
     * @return DoSomething
     */
    public static function fromData($data)
    {
        $header = new MessageHeader(Uuid::uuid4(), new \DateTime(), 1, 'test-case');

        return new static($header, array('data' => $data));
    }

    /**
     * @return string
     */
    public function data()
    {
        $arrayReader = new ArrayReader($this->payload);
        return $arrayReader->stringValue('data');
    }
}
 