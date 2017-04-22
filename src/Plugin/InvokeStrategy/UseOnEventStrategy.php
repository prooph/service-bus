<?php

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Messaging\DomainEvent;

interface UseOnEventStrategy
{
    public function onEvent(DomainEvent $message): void;
}
