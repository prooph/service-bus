<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 09/13/15 - 15:30
 */

namespace Prooph\ServiceBus\Plugin\Guard;

/**
 * Interface AuthorizationService
 * @package Prooph\ServiceBus\Plugin\Guard
 */
interface AuthorizationService
{
    /**
     * Check if the permission is granted to the current identity
     *
     * @param string $permission
     * @param mixed  $context
     * @return bool
     */
    public function isGranted($permission, $context = null);
}
