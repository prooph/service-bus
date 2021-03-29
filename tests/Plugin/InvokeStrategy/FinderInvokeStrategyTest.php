<?php

/**
 * This file is part of prooph/service-bus.
 * (c) 2014-2021 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\Plugin\InvokeStrategy\FinderInvokeStrategy;
use Prooph\ServiceBus\QueryBus;
use ProophTest\ServiceBus\Mock\CustomInvokableMessageHandler;
use ProophTest\ServiceBus\Mock\Finder;
use Prophecy\PhpUnit\ProphecyTrait;

class FinderInvokeStrategyTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_invokes_a_finder_which_has_method_named_like_the_query(): void
    {
        $queryBus = new QueryBus();

        $finderInvokeStrategy = new FinderInvokeStrategy();
        $finderInvokeStrategy->attachToMessageBus($queryBus);

        $finder = new Finder();

        $queryBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use ($finder): void {
                $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLER, $finder);
            },
            QueryBus::PRIORITY_INITIALIZE
        );

        $queryBus->dispatch('foo');
        $this->assertEquals('foo', $finder->getLastMessage());
    }

    /**
     * @test
     */
    public function it_should_not_handle_already_processed_messages(): void
    {
        $queryBus = new QueryBus();

        $finderInvokeStrategy = new FinderInvokeStrategy();
        $finderInvokeStrategy->attachToMessageBus($queryBus);

        $finder = new CustomInvokableMessageHandler();

        $queryBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent) use ($finder): void {
                $actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLER, $finder);
            },
            QueryBus::PRIORITY_INITIALIZE
        );

        $promise = $queryBus->dispatch('foo');

        $promise->otherwise(function ($ex) use (&$exception): void {
            $exception = $ex;
        });

        $this->assertNull($exception);

        $this->assertEquals('foo', $finder->getLastMessage());
        $this->assertSame(1, $finder->getInvokeCounter());
    }
}
