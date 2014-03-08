<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 19:10
 */

namespace Codeliner\ServiceBusTest\Message;

use Codeliner\ServiceBus\Message\Queue;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class QueueTest
 *
 * @package Codeliner\ServiceBusTest\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class QueueTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_name()
    {
        $queue = new Queue('TestQueue');

        $this->assertEquals('TestQueue', $queue->name());
    }
}
 