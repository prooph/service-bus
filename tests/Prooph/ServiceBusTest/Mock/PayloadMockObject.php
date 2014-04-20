<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:18
 */

namespace Prooph\ServiceBusTest\Mock;

use Prooph\ServiceBus\Message\PayloadInterface;

/**
 * Class PayloadMockObject
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class PayloadMockObject implements PayloadInterface
{
    /**
     * @var array
     */
    private $payload = array();

    /**
     * @param array $aPayload
     */
    public function __construct(array $aPayload)
    {
        $this->payload = $aPayload;
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->payload;
    }
}
 