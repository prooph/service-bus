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
namespace Prooph\ServiceBus\Process;

use Prooph\Common\Messaging\HasMessageName;
use Prooph\Common\Messaging\MessageHeader;
use Prooph\Common\Messaging\RemoteMessage;
use Prooph\ServiceBus\QueryBus;
use React\Promise\Deferred;

/**
 * Class QueryDispatch
 *
 * @package Prooph\ServiceBus\Process
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class QueryDispatch extends MessageDispatch
{
    const LOCATE_FINDER      = "locate-finder";
    const INVOKE_FINDER      = "invoke-finder";

    /**
     * @var Deferred
     */
    protected $deferred;

    /**
     * @param mixed $query
     * @param QueryBus $queryBus
     * @param Deferred $deferred
     * @throws \InvalidArgumentException
     * @return QueryDispatch
     */
    public static function initializeWith($query, QueryBus $queryBus, Deferred $deferred)
    {
        $instance = new static(static::INITIALIZE, $queryBus, array('message' => $query));

        if ($query instanceof HasMessageName) {
            $instance->setMessageName($query->messageName());
        }

        if ($query instanceof RemoteMessage) {
            if ($query->header()->type() !== MessageHeader::TYPE_QUERY) {
                throw new \InvalidArgumentException(
                    sprintf("Message %s cannot be handled. Message is not of type query.", $query->name())
                );
            }

            $instance->setMessageName($query->name());
        }

        $instance->deferred = $deferred;

        return $instance;
    }

    /**
     * @return string|null
     */
    public function getQueryName()
    {
        return $this->getParam('message-name');
    }

    /**
     * @param string $queryName
     * @throws \InvalidArgumentException
     */
    public function setQueryName($queryName)
    {
        $this->setMessageName($queryName);
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->getMessage();
    }

    /**
     * @param mixed $query
     */
    public function setQuery($query)
    {
        $this->setMessage($query);
    }

    /**
     * @return Deferred
     */
    public function getDeferred()
    {
        return $this->deferred;
    }

    /**
     * @return null|string|object|callable
     */
    public function getFinder()
    {
        return $this->getParam('query-finder');
    }

    /**
     * @param string|object|callable $finder
     * @throws \InvalidArgumentException
     */
    public function setFinder($finder)
    {
        if (! is_string($finder) && ! is_object($finder) && ! is_callable($finder)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid finder provided. Expected type is string, object or callable but type of %s given.",
                gettype($finder)
            ));
        }

        $this->setParam("query-finder", $finder);
    }
} 