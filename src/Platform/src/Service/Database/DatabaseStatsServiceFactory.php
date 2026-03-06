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

namespace Platform\Service\Database;

use Doctrine\DBAL\Connection;
use Neutrino\Repository\DatabaseRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

class DatabaseStatsServiceFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DatabaseStatsServiceInterface
    {
        $connection = $container->get(Connection::class);
        assert($connection instanceof Connection);

        $databases = $container->get(DatabaseRepository::class);
        assert($databases instanceof DatabaseRepository);

        $config = $container->get('config');
        $config = $config['doctrine']['connection']['orm_default']['params'] ?? null;

        return new DatabaseStatsService($connection, $databases, $config);
    }
}
