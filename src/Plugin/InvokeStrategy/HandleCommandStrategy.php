<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.03.14 - 21:41
 */

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

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
        return method_exists($handler, 'handle');
    }

    /**
     * @param mixed $handler
     * @param mixed $message
     */
    public function invoke($handler, $message)
    {
        $handler->handle($message);
    }
}
 