<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 21:04
 */

namespace Prooph\ServiceBusTest\Mock;

use Prooph\ServiceBus\Command\AbstractCommand;

/**
 * Class RemoveFileCommand
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
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
