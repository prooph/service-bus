<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 5/23/15 - 6:07 PM
 */
namespace Prooph\ServiceBusTest\InvokeStrategy;

use Prooph\ServiceBus\InvokeStrategy\FinderInvokeStrategy;
use Prooph\ServiceBus\Process\QueryDispatch;
use Prooph\ServiceBus\QueryBus;
use Prooph\ServiceBusTest\Mock\FetchSomething;
use Prooph\ServiceBusTest\Mock\FetchSomethingFinderMock;
use React\Promise\Deferred;

/**
 * Class FinderInvokeStrategyTest
 *
 * @package Prooph\ServiceBusTest\InvokeStrategy
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class FinderInvokeStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FinderInvokeStrategy
     */
    private $finderInvokeStrategy;

    /**
     * @var QueryDispatch
     */
    private $queryDispatch;

    protected function setUp()
    {
        $this->finderInvokeStrategy = new FinderInvokeStrategy();
        $this->queryDispatch = QueryDispatch::initializeWith(FetchSomething::fromData('test'), new QueryBus(), new Deferred());
    }

    /**
     * @test
     */
    function it_invokes_a_finder_which_has_method_named_like_the_query()
    {
        $fetchSomethingFinder = new FetchSomethingFinderMock();

        $this->queryDispatch->setFinder($fetchSomethingFinder);

        $this->finderInvokeStrategy->onInvokeFinder($this->queryDispatch);

        $this->assertSame($this->queryDispatch->getQuery(), $fetchSomethingFinder->getLastQuery());
        $this->assertSame($this->queryDispatch->getDeferred(), $fetchSomethingFinder->getLastDeferred());
    }

    /**
     * @test
     */
    function it_invokes_a_callable_array()
    {
        $fetchSomethingFinder = new FetchSomethingFinderMock();

        $finderSpec = [$fetchSomethingFinder, 'fetchSomething'];

        $this->queryDispatch->setFinder($finderSpec);

        $this->finderInvokeStrategy->onInvokeFinder($this->queryDispatch);

        $this->assertSame($this->queryDispatch->getQuery(), $fetchSomethingFinder->getLastQuery());
        $this->assertSame($this->queryDispatch->getDeferred(), $fetchSomethingFinder->getLastDeferred());
    }
} 