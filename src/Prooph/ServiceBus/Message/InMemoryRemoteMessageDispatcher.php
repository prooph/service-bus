<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.03.14 - 20:35
 */

namespace Prooph\ServiceBus\Message;

use Prooph\Common\Messaging\MessageHeader;
use Prooph\Common\Messaging\RemoteMessage;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;

/**
 * Class InMemoryRemoteMessageDispatcher
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class InMemoryRemoteMessageDispatcher implements RemoteMessageDispatcher
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EventBus
     */
    protected $eventBus;

    /**
     * @param CommandBus $commandBus
     * @param EventBus $eventBus
     */
    public function __construct(CommandBus $commandBus, EventBus $eventBus)
    {
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
    }

    /**
     * @param RemoteMessage $message
     * @throws \Exception If handling of message fails
     * @return void
     */
    public function dispatch(RemoteMessage $message)
    {

        if ($message->header()->type() === MessageHeader::TYPE_COMMAND) {

            $this->commandBus->dispatch($message);

            return;
        }

        if ($message->header()->type() === MessageHeader::TYPE_EVENT) {

            $this->eventBus->dispatch($message);

            return;
        }
    }
}
