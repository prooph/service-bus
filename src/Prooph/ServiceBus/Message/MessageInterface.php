<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:36
 */

namespace Prooph\ServiceBus\Message;

/**
 * Interface MessageInterface
 *
 * @deprecated This class will be removed in v4.0, use the one provided by prooph/common instead
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface MessageInterface
{
    /**
     * @param array $aMessageArray
     * @return MessageInterface
     */
    public static function fromArray(array $aMessageArray);

    /**
     * @return string
     */
    public function name();

    /**
     * @return MessageHeaderInterface
     */
    public function header();

    /**
     * @return array
     */
    public function payload();

    /**
     * @return array
     */
    public function toArray();
} 