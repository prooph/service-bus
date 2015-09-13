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
