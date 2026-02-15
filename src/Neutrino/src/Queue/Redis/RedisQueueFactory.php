<?php

declare(strict_types=1);

/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neutrino\Queue\Redis;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Redis;

use function assert;

final class RedisQueueFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RedisQueue
    {
        $redis = $container->get(Redis::class);
        assert($redis instanceof Redis);

        // You can configure the default queue name via config
        $config    = $container->get('config');
        $queueName = $config['redis']['queue_name'] ?? 'neutrino_default';

        return new RedisQueue($redis, $queueName);
    }
}
