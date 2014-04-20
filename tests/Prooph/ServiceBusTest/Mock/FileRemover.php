<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 21:23
 */

namespace Prooph\ServiceBusTest\Mock;

/**
 * Class FileRemover
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class FileRemover
{
    /**
     * @param RemoveFileCommand $aCommand
     */
    public function handleRemoveFileCommand(RemoveFileCommand $aCommand)
    {
        @unlink($aCommand->getFile());
    }
}
