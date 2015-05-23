<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 22.05.15 - 22:16
 */
namespace Prooph\ServiceBusTest\Process;

use Prooph\ServiceBus\Process\QueryDispatch;
use Prooph\ServiceBus\QueryBus;
use Prooph\ServiceBusTest\Mock\FetchSomething;
use React\Promise\Deferred;

/**
 * Class QueryDispatchTest
 *
 * @package Prooph\ServiceBusTest\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class QueryDispatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryDispatch
     */
    private $queryDispatch;

    /**
     * @var FetchSomething
     */
    private $query;

    /**
     * @var Deferred
     */
    private $deferred;

    /**
     * @var QueryBus
     */
    private $queryBus;

    protected function setUp()
    {
        $this->query = FetchSomething::fromData('test');
        $this->deferred = new Deferred();
        $this->queryBus = new QueryBus();
        $this->queryDispatch = QueryDispatch::initializeWith($this->query, $this->queryBus, $this->deferred);
    }

    /**
     * @test
     */
    public function it_is_initialized_with_a_query_and_a_query_bus_as_target()
    {
        $this->assertSame($this->query, $this->queryDispatch->getQuery());
        $this->assertSame($this->queryBus, $this->queryDispatch->getTarget());
        $this->assertEquals(QueryDispatch::INITIALIZE, $this->queryDispatch->getName());
    }

    /**
     * @test
     */
    public function it_replaces_query_with_a_new_one()
    {
        $otherQuery = new \ArrayObject(array('name' => 'FetchSomethingElse'));

        $this->queryDispatch->setQuery($otherQuery);

        $this->assertSame($otherQuery, $this->queryDispatch->getQuery());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_query_name()
    {
        $this->queryDispatch->setQueryName("FetchSomething");

        $this->assertEquals("FetchSomething", $this->queryDispatch->getQueryName());
    }

    /**
     * @test
     */
    public function it_only_accepts_a_string_as_query_name()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->queryDispatch->setQueryName(123);
    }

    /**
     * @test
     */
    public function it_sets_and_gets_a_finder_string()
    {
        $this->queryDispatch->setFinder("FetchSomethingFinder");

        $this->assertEquals("FetchSomethingFinder", $this->queryDispatch->getFinder());
    }

    /**
     * @test
     */
    public function it_accepts_an_object_as_finder()
    {
        $finder = new \stdClass();

        $this->queryDispatch->setFinder($finder);

        $this->assertSame($finder, $this->queryDispatch->getFinder());
    }

    /**
     * @test
     */
    public function it_accepts_a_callable_as_finder()
    {
        $finderCallback = function ($query) {};

        $this->queryDispatch->setFinder($finderCallback);

        $this->assertSame($finderCallback, $this->queryDispatch->getFinder());
    }

    /**
     * @test
     */
    public function it_does_not_accept_a_non_callable_array_as_finder()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->queryDispatch->setFinder(array("FetchSomethingFinder"));
    }
} 