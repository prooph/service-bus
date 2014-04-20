<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 17:19
 */

namespace Prooph\ServiceBus\Message\PhpResque;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Service\Definition;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBus\Service\StaticServiceBusRegistry;

/**
 * Class PhpResqueReceiverJob
 *
 * @package Prooph\ServiceBus\Message\PhpResque
 * @author Alexander Miertsch <contact@prooph.de>
 */
class PhpResqueReceiverJob
{
    /**
     * @var ServiceBusManager
     */
    protected $serviceBusManager;

    /**
     * Setup the environment to perform a message
     *
     * This method is required by Php_Resque and is called before a worker calls the {@method perform}
     */
    public function setUp()
    {
        $configuration = StaticServiceBusRegistry::getConfiguration();

        $this->serviceBusManager = new ServiceBusManager($configuration);
    }

    /**
     * Perform a message
     */
    public function perform()
    {
        $messageClass = $this->args['message_class'];

        /* @var $message \Prooph\ServiceBus\Message\MessageInterface */
        $message = $messageClass::fromArray($this->args['message_data']);

        if ($message->header()->type() === MessageHeader::TYPE_COMMAND) {
            $receiver = $this->serviceBusManager
                ->get(Definition::COMMAND_RECEIVER_MANAGER)
                ->get($message->header()->sender());
        } else {
            $receiver = $this->serviceBusManager
                ->get(Definition::EVENT_RECEIVER_MANAGER)
                ->get($message->header()->sender());
        }

        $receiver->handle($message);
    }
}
