<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 21:04
 */

namespace Codeliner\ServiceBusTest\Mock;

use Codeliner\ServiceBus\Command\AbstractCommand;

/**
 * Class RemoveFileCommand
 *
 * @package Codeliner\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class RemoveFileCommand extends AbstractCommand
{
    protected function convertPayload($aFile)
    {
        return array('file' => $aFile);
    }

    public function getFile()
    {
        return $this->payload['file'];
    }
}
