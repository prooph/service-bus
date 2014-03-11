<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 19:06
 */

namespace Codeliner\ServiceBusTest\Service;

use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\MessageDispatcherManager;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBusTest\TestCase;

/**
 * Class MessageDispatcherManagerTest
 *
 * @package Codeliner\ServiceBusTest\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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

        $this->assertInstanceOf('Codeliner\ServiceBus\Message\InMemoryMessageDispatcher', $inMemoryMessageDispatcher);
    }
}
