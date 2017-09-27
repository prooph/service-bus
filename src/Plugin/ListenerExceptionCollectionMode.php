<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin;

use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\MessageBus;

final class ListenerExceptionCollectionMode implements Plugin
{
    public function attachToMessageBus(MessageBus $messageBus): void
    {
        $this->assertEventBus($messageBus);
        /** @var EventBus $messageBus */
        $messageBus->enableCollectExceptions();
    }

    public function detachFromMessageBus(MessageBus $messageBus): void
    {
        $this->assertEventBus($messageBus);
        /** @var EventBus $messageBus */
        $messageBus->disableCollectExceptions();
    }

    private function assertEventBus(MessageBus $messageBus): void
    {
        if (! $messageBus instanceof EventBus) {
            throw new RuntimeException(__CLASS__ . ' can only be attached to an event bus.');
        }
    }
}
