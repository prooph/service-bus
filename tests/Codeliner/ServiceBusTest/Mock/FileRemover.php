<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 21:23
 */

namespace Codeliner\ServiceBusTest\Mock;

/**
 * Class FileRemover
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
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
