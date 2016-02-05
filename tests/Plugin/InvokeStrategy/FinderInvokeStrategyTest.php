<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 05/23/15 - 6:07 PM
 */

namespace ProophTest\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Event\DefaultActionEvent;
use Prooph\ServiceBus\Plugin\InvokeStrategy\FinderInvokeStrategy;
use Prooph\ServiceBus\QueryBus;
use ProophTest\ServiceBus\Mock\CustomMessage;
use ProophTest\ServiceBus\Mock\CustomMessageWithName;
use ProophTest\ServiceBus\Mock\Finder;
use React\Promise\Deferred;

/**
 * Class FinderInvokeStrategyTest
 *
 * @package ProophTest\ServiceBus\Plugin\InvokeStrategy
 */
final class FinderInvokeStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FinderInvokeStrategy
     */
    private $finderInvokeStrategy;

    /**
     * @var DefaultActionEvent
     */
    private $actionEvent;

    protected function setUp()
    {
        $this->finderInvokeStrategy = new FinderInvokeStrategy();

        $this->actionEvent = new DefaultActionEvent(QueryBus::EVENT_INVOKE_FINDER, new QueryBus(), [
            QueryBus::EVENT_PARAM_MESSAGE => new CustomMessage('I am a query'),
            QueryBus::EVENT_PARAM_MESSAGE_NAME => CustomMessage::class,
            QueryBus::EVENT_PARAM_DEFERRED => new Deferred(),
        ]);
    }

    /**
     * @test
     */
    public function it_invokes_a_finder_which_has_method_named_like_the_query()
    {
        $finder = new Finder();

        $this->actionEvent->setParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLER, $finder);

        $invokeStrategy = $this->finderInvokeStrategy;

        $invokeStrategy($this->actionEvent);

        $this->assertSame($this->actionEvent->getParam(QueryBus::EVENT_PARAM_MESSAGE), $finder->getLastMessage());
        $this->assertSame($this->actionEvent->getParam(QueryBus::EVENT_PARAM_DEFERRED), $finder->getLastDeferred());
        $this->assertTrue($this->actionEvent->getParam(QueryBus::EVENT_PARAM_MESSAGE_HANDLED));
    }

    /**
     * @test
     */
    public function it_determines_the_event_name_from_message_name_call_if_event_has_one()
    {
        $onEventStrategy = new FinderInvokeStrategy();
        $customEvent = new CustomMessageWithName("I am an event with a messageName() method");

        $closure = function ($event) {
            return $this->determineQueryName($event);
        };
        $determineEventName = $closure->bindTo($onEventStrategy, $onEventStrategy);

        $this->assertSame('CustomMessageWithSomeOtherName', $determineEventName($customEvent));
    }
}
