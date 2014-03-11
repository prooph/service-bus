<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:10
 */

namespace Codeliner\ServiceBus\Event;

use Rhumsaa\Uuid\Uuid;

/**
 * Interface EventInterface
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface EventInterface
{
    /**
     * @return Uuid
     */
    public function uuid();

    /**
     * @return int
     */
    public function version();

    /**
     * @return \DateTime
     */
    public function createdOn();

    /**
     * @return array
     */
    public function payload();
}
 