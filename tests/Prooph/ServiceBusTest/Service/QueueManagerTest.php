<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:19
 */

namespace Prooph\ServiceBusTest\Service;

use Prooph\ServiceBus\Service\QueueManager;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class QueueManagerTest
 *
 * @package Prooph\ServiceBusTest\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class QueueManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_a_valid_local_queue()
    {
        $queueManager = new QueueManager();
        $queueManager->setServiceLocator(new ServiceBusManager());

        $localQueue = $queueManager->get('local');

        $this->assertInstanceOf('Prooph\ServiceBus\Message\QueueInterface', $localQueue);
        $this->assertEquals('local', $localQueue->name());
    }
}
