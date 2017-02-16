<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2013-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

    /**
     * UnauthorizedException constructor.
     * @param string $messageName
     */
    public function __construct($messageName = '')
    {
        if (! empty($messageName)) {
            $this->message = 'You are not authorized to access the resource "' . $messageName . '"';
        }
    }
}
