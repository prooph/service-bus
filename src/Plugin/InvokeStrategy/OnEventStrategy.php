<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Messaging\HasMessageName;

/**
 * Class OnEventStrategy
 *
 * @package Prooph\ServiceBus\InvokeStrategy
 * @author Alexander Miertsch <contact@prooph.de>
 */
class OnEventStrategy extends AbstractInvokeStrategy
{
    /**
     * @param mixed $handler
     * @param mixed $message
     *
     * @return bool
     */
    public function canInvoke($handler, $message): bool
    {
        $handleMethod = 'on' . $this->determineEventName($message);

        return method_exists($handler, $handleMethod);
    }

    /**
     * @param mixed $handler
     * @param mixed $message
     *
     * @return void
     */
    public function invoke($handler, $message): void
    {
        $handleMethod = 'on' . $this->determineEventName($message);

        $handler->{$handleMethod}($message);
    }

    /**
     * @param mixed $event
     *
     * @return string
     */
    protected function determineEventName($event): string
    {
        $eventName = ($event instanceof HasMessageName)
            ? $event->messageName()
            : (is_object($event)? get_class($event): gettype($event));

        return implode('', array_slice(explode('\\', $eventName), -1));
    }
}
