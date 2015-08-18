<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 8/2/15 - 8:38 PM
 */
namespace Prooph\ServiceBusTest\Mock;

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
