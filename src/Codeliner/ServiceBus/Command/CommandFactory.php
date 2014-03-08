<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:55
 */

namespace Codeliner\ServiceBus\Command;
use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Message\MessageInterface;

/**
 * Class CommandFactory
 *
 * @package Codeliner\ServiceBus\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class CommandFactory implements CommandFactoryInterface
{
    /**
     * @param MessageInterface $aMessage
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException
     * @return CommandInterface
     */
    public function fromMessage(MessageInterface $aMessage)
    {
        $commandClass = $aMessage->name();

        if (!class_exists($commandClass)) {
            throw new RuntimeException(
                sprintf(
                    "Class for %s command can not be found",
                    $commandClass
                )
            );
        }

        return new $commandClass($aMessage->header(), $aMessage->payload());
    }
}
