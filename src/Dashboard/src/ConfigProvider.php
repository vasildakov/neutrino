<?php

namespace Dashboard;

use Dashboard\Handler\HomeHandler;
use Dashboard\Handler\HomeHandlerFactory;
use Dashboard\Handler\ShowDatabasesHandler;
use Dashboard\Handler\ShowDatabasesHandlerFactory;

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

    public function getDependencies(): array {
        return [
            'factories' => [
                HomeHandler::class => HomeHandlerFactory::class,
                ShowDatabasesHandler::class => ShowDatabasesHandlerFactory::class,

                // Services
                Service\Database\DatabaseStatsService::class => Service\Database\DatabaseStatsServiceFactory::class,
            ]
        ];
    }

    public function getDoctrineEntities(): array {
        return [];
    }

    public function getTemplates(): array {
        return [
            'paths' => [
                // Theme overrides FIRST
                'dashboard' => [
                    __DIR__ . '/../templates/dashboard',
                ],
                'layout' => [
                    __DIR__ . '/../templates/dashboard/layout', // theme override first// fallback
                ],
                'partial' => [
                    __DIR__ . '/../templates/dashboard/partial'
                ],
            ]
        ];
    }

    public function getCommands(): array {
        return [];
    }

    public function getRoutes(): array {
        return [
            [
                'name'       => 'dashboard.home',
                'path'       => '/dashboard',
                'middleware' => HomeHandler::class,
                'allowed_methods' => ['GET'],
            ],
        ];
    }
}