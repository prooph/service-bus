<?php

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin\InvokeStrategy;

use Prooph\Common\Messaging\Query;

interface UseFinderInvokeStrategy
{
    public function find(Query $query, string $deferred): void;
}
