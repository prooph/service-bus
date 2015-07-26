<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 5/23/15 - 6:17 PM
 */
namespace Prooph\ServiceBusTest;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\InvokeStrategy\FinderInvokeStrategy;
use Prooph\ServiceBus\InvokeStrategy\ForwardToRemoteMessageDispatcherStrategy;
use Prooph\ServiceBus\InvokeStrategy\ForwardToRemoteQueryDispatcherStrategy;
use Prooph\ServiceBus\Message\FromRemoteMessageTranslator;
use Prooph\ServiceBus\Message\InMemoryRemoteMessageDispatcher;
use Prooph\ServiceBus\Message\ProophDomainMessageToRemoteMessageTranslator;
use Prooph\ServiceBus\QueryBus;
use Prooph\ServiceBus\Router\QueryRouter;
use Prooph\ServiceBusTest\Mock\FetchSomething;
use Prooph\ServiceBusTest\Mock\FetchSomethingFinderMock;

/**
 * Class QueryBusTest
 *
 * @package Prooph\ServiceBusTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class QueryBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryBus
     */
    private $queryBus;

    protected function setUp()
    {
        $this->queryBus = new QueryBus();

        $queryToDispatcherRouter = new QueryRouter();

        $finderQueryBus = new QueryBus();

        $queryToDispatcherRouter->route(FetchSomething::class)->to(new InMemoryRemoteMessageDispatcher(
            new CommandBus(),
            new EventBus(),
            $finderQueryBus
        ));

        $this->queryBus->utilize($queryToDispatcherRouter);

        $this->queryBus->utilize(new ForwardToRemoteQueryDispatcherStrategy());

        $finder = new FetchSomethingFinderMock();

        $toFinderRouter = new QueryRouter();

        $toFinderRouter->route(FetchSomething::class)->to($finder);

        $finderQueryBus->utilize($toFinderRouter);

        $finderQueryBus->utilize(new FromRemoteMessageTranslator());

        $finderQueryBus->utilize(new FinderInvokeStrategy());
    }

    /**
     * @test
     */
    function it_dispatches_a_query_to_a_remote_query_dispatcher_which_then_resolves_the_deferred_by_dispatching_to_the_finder()
    {
        $query = FetchSomething::fromData('This should be the result');

        $promise = $this->queryBus->dispatch($query);

        $result = "wrong result";

        $promise->done(function ($resolvedResult) use (&$result) {
            $result = $resolvedResult;
        });

        $this->assertEquals('This should be the result', $result);
    }
} 