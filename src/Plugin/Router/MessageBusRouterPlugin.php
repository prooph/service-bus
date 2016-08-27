<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/26/16 - 6:08 PM
 */

namespace Prooph\ServiceBus\Plugin\Router;

use Prooph\Common\Event\ActionEvent;

interface MessageBusRouterPlugin
{
    /**
     * Handle route action event of a message bus dispatch
     *
     * @param ActionEvent $actionEvent
     * @return void
     */
    public function onRouteMessage(ActionEvent $actionEvent);
}
