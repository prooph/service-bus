<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 17:19
 */

namespace Codeliner\ServiceBus\Message\PhpResque;
use Codeliner\ServiceBus\Message\MessageHeader;
use Codeliner\ServiceBus\Service\Definition;
use Codeliner\ServiceBus\Service\ServiceBusManager;
use Codeliner\ServiceBus\Service\StaticServiceBusRegistry;

/**
 * Class PhpResqueReceiverJob
 *
 * @package Codeliner\ServiceBus\Message\PhpResque
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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

        /* @var $message \Codeliner\ServiceBus\Message\MessageInterface */
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
