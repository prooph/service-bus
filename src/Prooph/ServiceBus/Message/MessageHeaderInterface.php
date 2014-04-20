<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:11
 */

namespace Prooph\ServiceBus\Message;

use Rhumsaa\Uuid\Uuid;
use ValueObjects\DateTime\DateTime;

/**
 * Interface MessageHeaderInterface
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface MessageHeaderInterface
{
    /**
     * @param array $aMessageHeaderArray
     * @return MessageHeaderInterface
     */
    public static function fromArray(array $aMessageHeaderArray);

    /**
     * @return Uuid
     */
    public function uuid();

    /**
     * @return DateTime
     */
    public function createdOn();

    /**
     * @return int
     */
    public function version();

    /**
     * @return string
     */
    public function sender();

    /**
     * @return string
     */
    public function type();

    /**
     * @param MessageHeaderInterface $other
     * @return bool
     */
    public function sameHeaderAs(MessageHeaderInterface $other);

    /**
     * @return array
     */
    public function toArray();
}