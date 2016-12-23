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

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\Plugin\InvokeStrategy\FinderInvokeStrategy;
use Prooph\ServiceBus\QueryBus;
use ProophTest\ServiceBus\Mock\Finder;

class FinderInvokeStrategyTest extends TestCase
{
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
}
