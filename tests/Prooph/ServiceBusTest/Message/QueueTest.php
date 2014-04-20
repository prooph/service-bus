<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 19:10
 */

namespace Prooph\ServiceBusTest\Message;

use Prooph\ServiceBus\Message\Queue;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class QueueTest
 *
 * @package Prooph\ServiceBusTest\Message
 * @author Alexander Miertsch <contact@prooph.de>
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
 