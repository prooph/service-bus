<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:19
 */

namespace Codeliner\ServiceBusTest\Service;

use Codeliner\ServiceBus\Service\QueueManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class QueueManagerTest
 *
 * @package Codeliner\ServiceBusTest\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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

        $this->assertInstanceOf('Codeliner\ServiceBus\Message\QueueInterface', $localQueue);
        $this->assertEquals('local', $localQueue->name());
    }
}
