<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\ServiceBus\Plugin\CacheKeyGenerator;

use Prooph\Common\Messaging\Query;
use Prooph\ServiceBus\Plugin\CacheKeyGenerator;

final class Standard implements CacheKeyGenerator
{
    public function getCacheKey(Query $query): string
    {
        $keyParts = [$query->messageName()];
        foreach ($query->payload() as $key => $value) {
            $keyParts[] = $key;
            $keyParts[] = $value;
        }

        return implode('.', array_map([$this, 'sanitizeKeyPart'], $keyParts));
    }

    private function sanitizeKeyPart(string $part)
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '_', $part);
    }
}
