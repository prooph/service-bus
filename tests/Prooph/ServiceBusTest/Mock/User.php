<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.04.14 - 01:15
 */

namespace Prooph\ServiceBusTest\Mock;

use Prooph\EventStore\EventSourcing\EventSourcedAggregateRoot;
use Rhumsaa\Uuid\Uuid;

/**
 * Class User
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class User extends EventSourcedAggregateRoot
{
    protected $id;

    protected $name;

    public function __construct($name)
    {
        $id = Uuid::uuid4();

        $this->apply(new UserCreated($id, array('name' => $name)));
    }

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    protected function onUserCreated(UserCreated $e)
    {
        $this->id = $e->aggregateId();
        $this->name = $e->getName();
    }
}
 