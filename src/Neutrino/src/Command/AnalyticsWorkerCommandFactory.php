<?php

declare(strict_types=1);

namespace Neutrino\Command;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Neutrino\Queue\Driver\Redis\RedisStreamsConsumer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

use function assert;

final class AnalyticsWorkerCommandFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AnalyticsWorkerCommand
    {
        $logger = $container->get(LoggerInterface::class);
        assert($logger instanceof LoggerInterface);

        $consumer = $container->get(RedisStreamsConsumer::class);
        assert($consumer instanceof RedisStreamsConsumer);

        $em = $container->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);

        if ($logger instanceof Logger) {
            $logger->pushHandler(new StreamHandler('./var/log/analytics-worker.log'));
        }

        return new AnalyticsWorkerCommand($logger, $consumer, $em);
    }
}
