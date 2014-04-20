<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:33
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
