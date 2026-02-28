<?php

declare(strict_types=1);

namespace Neutrino\Analytics;

use Neutrino\Queue\Driver\Redis\RedisStreamsQueue;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

class RedisQueueAnalyticsWriterFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RedisQueueAnalyticsWriter
    {
        $queue = $container->get(RedisStreamsQueue::class);
        assert($queue instanceof RedisStreamsQueue);

        return new RedisQueueAnalyticsWriter(
            queue:  $queue,
            queueName: 'neutrino.analytics' // you can make this configurable if you want
        );
    }
}
