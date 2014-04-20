<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 11:42
 */

namespace Prooph\ServiceBus\Command;

use Rhumsaa\Uuid\Uuid;
use ValueObjects\DateTime\DateTime;

/**
 * Interface CommandInterface
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface CommandInterface
{
    /**
     * @return Uuid
     */
    public function uuid();

    /**
     * @return int
     */
    public function version();

    /**
     * @return DateTime
     */
    public function createdOn();

    /**
     * @return array
     */
    public function payload();
}