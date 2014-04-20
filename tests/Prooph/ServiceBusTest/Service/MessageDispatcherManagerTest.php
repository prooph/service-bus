<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:06
 */

namespace Prooph\ServiceBusTest\Service;

use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\MessageDispatcherManager;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBusTest\TestCase;

/**
 * Class MessageDispatcherManagerTest
 *
 * @package Prooph\ServiceBusTest\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class MessageDispatcherManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_an_in_memory_message_dispatcher()
    {
        $messageDispatcherManager = new MessageDispatcherManager();
        $messageDispatcherManager->setServiceLocator(new ServiceBusManager());

        $inMemoryMessageDispatcher = $messageDispatcherManager->get(Definition::IN_MEMORY_MESSAGE_DISPATCHER);

        $this->assertInstanceOf('Prooph\ServiceBus\Message\InMemoryMessageDispatcher', $inMemoryMessageDispatcher);
    }
}
