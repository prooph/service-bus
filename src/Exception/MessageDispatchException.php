<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Exception;

use Prooph\Common\Event\ActionEvent;

class MessageDispatchException extends RuntimeException
{
    /**
     * @var ActionEvent
     */
    protected $actionEvent;

    public static function failed(ActionEvent $actionEvent, ?\Throwable $previousException): MessageDispatchException
    {
        $ex = new static(
            sprintf(
                "Message dispatch failed during %s phase.%s",
                $actionEvent->getName(),
                (null === $previousException) ? '' : ' Error: ' . $previousException->getMessage()
            ),
            422,
            $previousException
        );

        $ex->setFailedDispatch($actionEvent);

        return $ex;
    }

    public function getFailedDispatchEvent(): ActionEvent
    {
        return $this->actionEvent;
    }

    protected function setFailedDispatch(ActionEvent $actionEvent): void
    {
        $this->actionEvent = $actionEvent;
    }
}
