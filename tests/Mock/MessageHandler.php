<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2013-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProophTest\ServiceBus\Mock;

/**
 * Class MessageHandler
 * @package ProophTest\ServiceBus\Mock
 */
final class MessageHandler
{
    private $lastMessage;

    private $invokeCounter = 0;

    public function __invoke($message)
    {
        $this->lastMessage = $message;
        $this->invokeCounter++;
    }

    public function handle($message)
    {
        $this->lastMessage = $message;
        $this->invokeCounter++;
    }

    public function onCustomMessage($message)
    {
        $this->lastMessage = $message;
        $this->invokeCounter++;
    }

    public function getInvokeCounter()
    {
        return $this->invokeCounter;
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }
}
