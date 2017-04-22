<?php

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Messaging\Command;

interface UseHandleCommandStrategy
{
    public function handle(Command $message): void;
}
