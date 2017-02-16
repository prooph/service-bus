<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin;

use Prooph\ServiceBus\MessageBus;

abstract class AbstractPlugin implements Plugin
{
    /**
     * @var array
     */
    protected $listenerHandlers = [];

    public function detachFromMessageBus(MessageBus $messageBus): void
    {
        foreach ($this->listenerHandlers as $listenerHandler) {
            $messageBus->detach($listenerHandler);
        }

        $this->listenerHandlers = [];
    }
}
