<?php

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
