<?php

declare(strict_types=1);

namespace Platform\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Entity\Database;
use Neutrino\Repository\DatabaseRepository;
use Platform\Service\Database\DatabaseStatsService;
use Platform\Service\Database\DatabaseStatsServiceInterface;
use Psr\Container\ContainerInterface;

class ShowDatabasesHandlerFactory
{
    public function __invoke(ContainerInterface $container): ShowDatabasesHandler
    {
        $config = $container->get('config');

        $config = $config['doctrine']['connection']['orm_default']['params'] ?? null;


        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $connection = $container->get('neutrino.admin.connection');
        assert($connection instanceof Connection);

        $databaseRepository = $container->get(DatabaseRepository::class);
        assert($databaseRepository instanceof DatabaseRepository);

        $service = $container->get(DatabaseStatsService::class);
        assert($service instanceof DatabaseStatsServiceInterface);

        return new ShowDatabasesHandler(
            $template,
            $service
        );
    }
}