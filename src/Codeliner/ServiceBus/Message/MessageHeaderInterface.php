<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 15:11
 */

namespace Codeliner\ServiceBus\Message;

use Rhumsaa\Uuid\Uuid;

/**
 * Interface MessageHeaderInterface
 *
 * @package Codeliner\ServiceBus\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface MessageHeaderInterface
{
    /**
     * @return Uuid
     */
    public function uuid();

    /**
     * @return \DateTime
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
     * @param MessageHeaderInterface $other
     * @return bool
     */
    public function sameHeaderAs(MessageHeaderInterface $other);
}