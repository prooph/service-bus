<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:33
 */

namespace Codeliner\ServiceBus\Exception;

/**
 * Class RuntimeException
 *
 * @package Codeliner\ServiceBus\Exception
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class RuntimeException extends \RuntimeException implements ServiceBusException
{
}
