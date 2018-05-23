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

namespace Prooph\ServiceBus\Plugin;

use Prooph\Common\Event\ActionEvent;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\QueryBus;
use Psr\SimpleCache\CacheInterface;

final class CachePlugin extends AbstractPlugin
{
    private $cache;
    private $keyGenerator;

    public function __construct(
        CacheInterface $cache,
        CacheKeyGenerator $keyGenerator = null
    ) {
        $this->cache = $cache;
        $this->keyGenerator = $keyGenerator ?: new CacheKeyGenerator\Standard();
    }

    public function attachToMessageBus(MessageBus $messageBus): void
    {
        if (! $messageBus instanceof QueryBus) {
            throw new \InvalidArgumentException(sprintf(
                'The cache plugin can only be attached to an instance of "Prooph\ServiceBus\QueryBus", got "%s".',
                get_class($messageBus)
            ));
        }

        $this->listenerHandlers[] = $messageBus->attach(
            QueryBus::EVENT_DISPATCH,
            function (ActionEvent $actionEvent): void {
                if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED, false)) {
                    return;
                }

                $query = $actionEvent->getParam(QueryBus::EVENT_PARAM_MESSAGE);

                $deferred = $actionEvent->getParam(QueryBus::EVENT_PARAM_DEFERRED);

                $key = $this->keyGenerator->getCacheKey($query);
                if (null !== $result = $this->cache->get($key)) {
                    $deferred->resolve($result);
                    $actionEvent->setParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLER, null);

                    return;
                }

                $deferred->promise()->then(function ($data) use ($key) {
                    $this->cache->set($key, $data);
                });
            },
            QueryBus::PRIORITY_INVOKE_HANDLER + 1
        );
    }
}
