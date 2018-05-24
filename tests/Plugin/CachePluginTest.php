<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\Query;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Plugin\CacheKeyGenerator;
use Prooph\ServiceBus\Plugin\CachePlugin;
use Prooph\ServiceBus\Plugin\Plugin;
use Prooph\ServiceBus\Plugin\Router\QueryRouter;
use Prooph\ServiceBus\QueryBus;
use Psr\SimpleCache\CacheInterface;
use React\Promise\Deferred;

class CachePluginTest extends TestCase
{
    private $cache;
    private $keyGen;
    private $plugin;

    public function setUp()
    {
        $this->cache = $this->prophesize(CacheInterface::class);
        $this->keyGen = $this->prophesize(CacheKeyGenerator::class);
        $this->plugin = new CachePlugin(
            $this->cache->reveal(),
            $this->keyGen->reveal()
        );
    }

    /**
     * @test
     */
    public function it_is_a_message_bus_plugin()
    {
        $this->assertInstanceOf(Plugin::class, $this->plugin);
    }

    /**
     * @test
     */
    public function it_resolves_with_cached_value_on_hit()
    {
        $queryBus = new QueryBus();

        $router = new QueryRouter();
        $router->route('a-query')
           ->to(function ($query, Deferred $deferred): void {
           });
        $router->attachToMessageBus($queryBus);
        $query = $this->prophesize(Query::class);
        $this->plugin->attachToMessageBus($queryBus);

        $query->messageName()->willReturn('a-query');
        $this->keyGen->getCacheKey($query)->willReturn('cache-key');
        $this->cache->get('cache-key')->willReturn('Hello World');

        $promise = $queryBus->dispatch($query->reveal());
        $value = null;
        $promise->then(function ($result) use (&$value) {
            $value = $result;
        });
        $this->assertEquals('Hello World', $value);
    }

    /**
     * @test
     */
    public function it_caches_resolved_value()
    {
        $queryBus = new QueryBus();

        $router = new QueryRouter();
        $router->route('a-query')
            ->to(function ($query, Deferred $deferred): void {
                $deferred->resolve('Hello World');
            });
        $router->attachToMessageBus($queryBus);
        $query = $this->prophesize(Query::class);
        $this->plugin->attachToMessageBus($queryBus);

        $query->messageName()->willReturn('a-query');
        $this->keyGen->getCacheKey($query)->willReturn('cache-key');
        $this->cache->get('cache-key')->willReturn(null);
        $this->cache->set('cache-key', 'Hello World')->shouldBeCalled();

        $queryBus->dispatch($query->reveal());
    }

    /**
     * @test
     */
    public function it_does_not_attach_to_command_bus()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->plugin->attachToMessageBus(new CommandBus());
    }

    /**
     * @test
     */
    public function it_does_not_attach_to_event_bus()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->plugin->attachToMessageBus(new EventBus());
    }
}
