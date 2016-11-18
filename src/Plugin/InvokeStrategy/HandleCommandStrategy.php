<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Messaging\HasMessageName;

class HandleCommandStrategy extends AbstractInvokeStrategy
{
    /**
     * @param mixed $handler
     * @param mixed $message
     *
     * @return bool
     */
    public function canInvoke($handler, $message): bool
    {
        $handleMethod = 'handle' . $this->determineCommandName($message);

        return method_exists($handler, $handleMethod) || method_exists($handler, 'handle');
    }

    /**
     * @param mixed $handler
     * @param mixed $message
     *
     * @return void
     */
    public function invoke($handler, $message): void
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
     *
     * @return string
     */
    protected function determineCommandName($message): string
    {
        $eventName = ($message instanceof HasMessageName)
            ? $message->messageName()
            : (is_object($message)? get_class($message): gettype($message));

        return implode('', array_slice(explode('\\', $eventName), -1));
    }
}
