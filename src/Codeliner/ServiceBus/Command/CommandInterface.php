<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 11:42
 */

namespace Codeliner\ServiceBus\Command;

use Codeliner\ServiceBus\Message\MessageHeaderInterface;

/**
 * Interface CommandInterface
 *
 * @package Codeliner\ServiceBus\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface CommandInterface
{
    /**
     * @return MessageHeaderInterface
     */
    public function header();

    /**
     * @return array
     */
    public function payload();
}