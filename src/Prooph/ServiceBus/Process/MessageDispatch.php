<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 13.01.15 - 15:06
 */

namespace Prooph\ServiceBus\Process;

use Assert\Assertion;
use Prooph\ServiceBus\Exception\RuntimeException;
use Zend\EventManager\Event as ProcessEvent;
use Psr\Log\LoggerInterface;
use Zend\Log\Logger;

/**
 * Class MessageDispatch
 *
 * Basic implementation of a dispatch event used in the event-driven dispatch process of a message bus.
 *
 * @package Prooph\ServiceBus\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class MessageDispatch extends ProcessEvent
{
    const INITIALIZE          = "initialize";
    const DETECT_MESSAGE_NAME = "detect-message-name";
    const ROUTE               = "route";
    const HANDLE_ERROR        = "handle-error";
    const FINALIZE            = "finalize";

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $isLoggingEnabled = false;

    /**
     * @return null|string
     */
    public function getMessageName()
    {
        return $this->getParam('message-name');
    }

    /**
     * @param string $messageName
     */
    public function setMessageName($messageName)
    {
        Assertion::string($messageName);
        Assertion::notEmpty($messageName);

        $this->setParam('message-name', $messageName);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->getParam('message');
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->setParam('message', $message);
    }

    /**
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            throw new RuntimeException('No logger available');
        }

        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function useLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->isLoggingEnabled = true;
    }

    /**
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->isLoggingEnabled;
    }

    /**
     * @param \Exception $exception
     * @return $this
     */
    public function setException(\Exception $exception)
    {
        $this->setParam('exception', $exception);
    }

    /**
     * @return null|\Exception
     */
    public function getException()
    {
        return $this->getParam('exception');
    }
}
 