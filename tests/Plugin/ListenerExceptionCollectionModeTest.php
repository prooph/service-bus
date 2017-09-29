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

namespace ProophTest\ServiceBus\Plugin;

use PHPUnit\Framework\TestCase;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\ListenerExceptionCollectionMode;

class ListenerExceptionCollectionModeTest extends TestCase
{
    private $eventBus;

    protected function setUp(): void
    {
        $this->eventBus = new class() extends EventBus {
            public function isCollectExceptionsModeOn(): bool
            {
                return (bool) $this->collectExceptions;
            }
        };

        $this->cut = new ListenerExceptionCollectionMode();
    }

    /**
     * @test
     */
    public function it_enables_collect_exceptions_mode_if_attached_and_disables_mode_if_detached_again(): void
    {
        $plugin = new ListenerExceptionCollectionMode();
        $plugin->attachToMessageBus($this->eventBus);
        $this->assertTrue($this->eventBus->isCollectExceptionsModeOn());
        $plugin->detachFromMessageBus($this->eventBus);
        $this->assertFalse($this->eventBus->isCollectExceptionsModeOn());
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_exception_if_message_bus_is_not_an_event_bus(): void
    {
        $plugin = new ListenerExceptionCollectionMode();
        $plugin->attachToMessageBus(new CommandBus());
    }
}
