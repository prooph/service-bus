<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:18
 */

namespace Codeliner\ServiceBusTest\Mock;

use Codeliner\ServiceBus\Message\PayloadInterface;

/**
 * Class PayloadMockObject
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
 