<?php

declare(strict_types=1);

namespace Platform;

use Platform\Handler\HomeHandler;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineEntities(),
            'templates'    => $this->getTemplates(),
            'laminas-cli'  => $this->getCommands(),
            'routes'       => $this->getRoutes(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                HomeHandler::class                  => Handler\HomeHandlerFactory::class,
                Handler\ShowDatabasesHandler::class => Handler\ShowDatabasesHandlerFactory::class,
                Handler\QueueHandler::class         => Handler\QueueHandlerFactory::class,
                Handler\ListAccounts::class         => Handler\ListAccountsFactory::class,
                Handler\ShowUserHandler::class      => Handler\ShowUserHandlerFactory::class,

                // Services
                Service\Database\DatabaseStatsService::class => Service\Database\DatabaseStatsServiceFactory::class,
            ],
        ];
    }

    public function getDoctrineEntities(): array
    {
        return [];
    }

    public function getTemplates(): array
    {
        return [
            'map' => [
                'platform::sidebar' => __DIR__ . '/../templates/platform/partial/sidebar.phtml',
            ],
            'paths' => [
                // Theme overrides FIRST
                'platform' => [
                    __DIR__ . '/../templates/platform',
                ],
                'layout'   => [
                    __DIR__ . '/../templates/platform/layout', // theme override first// fallback
                ],
                'partial'  => [
                    __DIR__ . '/../templates/platform/partial',
                ],
            ],
        ];
    }

    public function getCommands(): array
    {
        return [];
    }

    public function getRoutes(): array
    {
        return [
            [
                'name'            => 'platform.home',
                'path'            => '/platform',
                'middleware'      => HomeHandler::class,
                'allowed_methods' => ['GET'],
            ],
        ];
    }
}
