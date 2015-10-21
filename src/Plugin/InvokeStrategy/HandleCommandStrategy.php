<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 03/09/14 - 21:41
 */

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Messaging\HasMessageName;

/**
 * Class HandleCommandStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <contact@prooph.de>
 */
class HandleCommandStrategy extends AbstractInvokeStrategy
{
    /**
     * @param mixed $handler
     * @param mixed $message
     * @return bool
     */
    public function canInvoke($handler, $message)
    {
        $handleMethod = 'handle' . $this->determineCommandName($message);

        return method_exists($handler, $handleMethod) || method_exists($handler, 'handle');
    }

    /**
     * @param mixed $handler
     * @param mixed $message
     */
    public function invoke($handler, $message)
    {
        $handleMethod = 'handle' . $this->determineCommandName($message);

        if (method_exists($handler, $handleMethod)) {
            $handler->{$handleMethod}($message);
        } else {
            $handler->handle($message);
        }
    }

    /**
     * @param mixed $message
     * @return string
     */
    protected function determineCommandName($message)
    {
        $eventName = ($message instanceof HasMessageName)? $message->messageName() : is_object($message)? get_class($message) : gettype($message);
        return implode('', array_slice(explode('\\', $eventName), -1));
    }
}
