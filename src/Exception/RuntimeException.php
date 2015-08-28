<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 03/08/14 - 20:33
 */

namespace Prooph\ServiceBus\Exception;

/**
 * Class RuntimeException
 *
 * @package Prooph\ServiceBus\Exception
 * @author Alexander Miertsch <contact@prooph.de>
 */
class RuntimeException extends \RuntimeException implements ServiceBusException
{
}
