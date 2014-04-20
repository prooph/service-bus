<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:55
 */

namespace Prooph\ServiceBus\Command;
use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageInterface;

/**
 * Class CommandFactory
 *
 * @package Prooph\ServiceBus\Command
 * @author Alexander Miertsch <contact@prooph.de>
 */
class CommandFactory implements CommandFactoryInterface
{
    /**
     * @param MessageInterface $aMessage
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
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

        return new $commandClass(
            $aMessage->payload(),
            $aMessage->header()->version(),
            $aMessage->header()->uuid(),
            $aMessage->header()->createdOn()
        );
    }
}
