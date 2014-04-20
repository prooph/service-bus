<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 13:21
 */

namespace Prooph\ServiceBus\Message\PhpResque;

use Prooph\ServiceBus\Message\MessageDispatcherInterface;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Message\QueueInterface;
use Prooph\ServiceBus\Service\Definition;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Class PhpResqueMessageDispatcher
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class PhpResqueMessageDispatcher implements MessageDispatcherInterface
{
    /**
     * @var string
     */
    protected $receiverJobClass = 'Prooph\ServiceBus\Message\PhpResque\PhpResqueReceiverJob';

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var bool
     */
    protected $trackStatus = false;

    /**
     * @param null|array $options
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            if (isset($options['receiver_job_class'])) {
                $this->receiverJobClass = $options['receiver_job_class'];
            }

            if (isset($options['track_job_status'])) {
                $this->trackStatus = (bool)$options['track_job_status'];
            }
        }
    }

    /**
     * @param QueueInterface $aQueue
     * @param MessageInterface $aMessage
     * @return void
     */
    public function dispatch(QueueInterface $aQueue, MessageInterface $aMessage)
    {
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('queue' => $aQueue, 'message' => $aMessage));

        $payload = array(
            'message_class' => get_class($aMessage),
            'message_data'  => $aMessage->toArray()
        );

        $jobId = \Resque::enqueue($aQueue->name(), $this->receiverJobClass, $payload, $this->trackStatus);

        $this->events()->trigger(
            __FUNCTION__ . '.post',
            $this,
            array('queue' => $aQueue, 'message' => $aMessage, 'jobId' => $jobId)
        );
    }

    /**
     * @return EventManagerInterface
     */
    public function events()
    {
        if (is_null($this->events)) {
            $this->events = new EventManager(array(
                Definition::SERVICE_BUS_COMPONENT,
                'message_dispatcher',
                __CLASS__
            ));
        }

        return $this->events;
    }

    /**
     * @return void
     */
    public function activateJobTracking()
    {
        $this->trackStatus = true;
    }

    /**
     * @return void
     */
    public function deactivateJobTracking()
    {
        $this->trackStatus = false;
    }
}
 