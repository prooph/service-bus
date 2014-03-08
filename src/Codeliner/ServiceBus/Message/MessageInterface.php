<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:36
 */

namespace Codeliner\ServiceBus\Message;

/**
 * Interface MessageInterface
 *
 * @package Codeliner\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface MessageInterface
{
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
} 