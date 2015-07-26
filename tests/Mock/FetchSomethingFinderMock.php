<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 5/23/15 - 6:09 PM
 */
namespace Prooph\ServiceBusTest\Mock;
use Prooph\Common\Messaging\Query;
use React\Promise\Deferred;

/**
 * Class FetchSomethingFinderMock
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FetchSomethingFinderMock 
{
    /**
     * @var Query
     */
    private $lastQuery;

    /**
     * @var Deferred
     */
    private $lastDeferred;

    public function fetchSomething(FetchSomething $query, Deferred $deferred)
    {
        $this->lastQuery = $query;
        $this->lastDeferred = $deferred;
        $this->lastDeferred->resolve($query->data());
    }

    /**
     * @return \React\Promise\Deferred
     */
    public function getLastDeferred()
    {
        return $this->lastDeferred;
    }

    /**
     * @return \Prooph\Common\Messaging\Query
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }
} 