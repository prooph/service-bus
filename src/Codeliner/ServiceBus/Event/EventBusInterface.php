<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 22:58
 */

namespace Codeliner\ServiceBus\Event;

/**
 * Interface EventBusInterface
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
 