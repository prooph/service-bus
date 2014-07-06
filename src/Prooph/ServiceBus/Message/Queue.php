<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 19:07
 */

namespace Prooph\ServiceBus\Message;

/**
 * Class Queue
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class Queue implements QueueInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $aName
     */
    public function __construct($aName)
    {
        \Assert\that($aName)->notEmpty('Queue.name must not be empty')->string('Queue.name must be a string');

        $this->name = $aName;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }
}
