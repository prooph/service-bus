<?php

namespace Prooph\ServiceBus\Plugin\Guard;

/**
 * Class UnauthorizedException
 * @package Prooph\ServiceBus\Plugin\Guard
 */
final class UnauthorizedException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $message = 'You are not authorized to access this resource';
}
