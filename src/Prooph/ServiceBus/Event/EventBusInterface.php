<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:58
 */

namespace Prooph\ServiceBus\Event;

/**
 * Interface EventBusInterface
 *
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface EventBusInterface
{
    /**
     * @param EventInterface $anEvent
     *
     * @return void
     */
    public function publish(EventInterface $anEvent);
}
 