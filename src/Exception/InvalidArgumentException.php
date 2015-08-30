<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/28/15 - 21:43
 */

namespace Prooph\ServiceBus\Exception;

/**
 * Class InvalidArgumentException
 *
 * @package Prooph\ServiceBus\Exception
 */
class InvalidArgumentException extends \InvalidArgumentException implements ServiceBusException
{
}
