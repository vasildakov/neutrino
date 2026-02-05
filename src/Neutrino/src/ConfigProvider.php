<?php

declare(strict_types=1);

namespace Neutrino;

use Neutrino\Handler\Register\RegisterService;
use Neutrino\Entity;
use Neutrino\Handler;
use Neutrino\Repository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Psr\Container\ContainerInterface;

/**
 * The configuration provider for the App module
 */
final class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
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

    /**
     * Returns the container dependencies
     *
     * @return array<string,mixed>
     */
    public function getDependencies(): array
    {
        return [
            'aliases'    => [
                'doctrine.entity_manager.orm_default' => EntityManagerInterface::class,
            ],
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'factories'  => [
                // Neutrino: admin connection using root credentials
                'neutrino.admin.connection' => function (ContainerInterface $container): Connection {
                    return DriverManager::getConnection([
                        'driver'   => 'pdo_mysql',
                        'host'     => $_ENV['MYSQL_HOST'],
                        'port'     => $_ENV['MYSQL_PORT'],
                        'user'     => $_ENV['MYSQL_ROOT_USER'],
                        'password' => $_ENV['MYSQL_ROOT_PASSWORD'],
                    ]);
                },
                // Handlers
                Handler\HomePageHandler::class => Handler\HomePageHandlerFactory::class,

                Handler\Login\LoginFormHandler::class => Handler\Login\LoginFormHandlerFactory::class,
                Handler\Login\LoginHandler::class => Handler\Login\LoginHandlerFactory::class,

                Handler\Register\RegisterHandler::class => Handler\Register\RegisterHandlerFactory::class,
                Handler\Register\RegisterFormHandler::class => Handler\Register\RegisterFormHandlerFactory::class,
                RegisterService::class => function (ContainerInterface $container): RegisterService {
                    return new RegisterService(
                        $container->get(EntityManagerInterface::class)
                    );
                },


                // Services
                Service\Migrator::class => Service\MigratorFactory::class,


                Tenant\TenantMigrationsRunner::class => Tenant\TenantMigrationsRunnerFactory::class,
                Tenant\TenantEntityManagerFactory::class => function (ContainerInterface $container): Tenant\TenantEntityManagerFactory {
                    return new Tenant\TenantEntityManagerFactory([], true);
                },

                // Commands
                Command\CreateDatabaseCommand::class => Command\CreateDatabaseCommandFactory::class,
                Command\DropDatabaseCommand::class => Command\DropDatabaseCommandFactory::class,
                Command\MigrateTenantsCommand::class => Command\MigrateTenantsCommandFactory::class,

                //
                Repository\DatabaseRepository::class => function (ContainerInterface $container): Repository\DatabaseRepository {
                    $em = $container->get(EntityManagerInterface::class);
                    $metadata = $em->getClassMetadata(Entity\Database::class);
                    return new Repository\DatabaseRepository($em, $metadata);
                },
            ],
        ];
    }

    /**
     * Returns the route configuration
     * @return list<array<string, mixed>>
     */
    public function getRoutes(): array
    {
        return [
            [
                'name' => 'app.login',
                'path' => '/login',
                'middleware' => [
                    BodyParamsMiddleware::class,
                    Handler\Login\LoginHandler::class
                ],
                'allowed_methods' => ['GET', 'POST'],
            ],
            [
                'name' => 'app.register',
                'path' => '/register',
                'middleware' => [
                    BodyParamsMiddleware::class,
                    Handler\Register\RegisterHandler::class
                ],
                'allowed_methods' => ['GET', 'POST'],
            ],
        ];
    }

    /**
     * Returns the console commands
     *
     * @return array<string,array<string,string>>
     */
    public function getCommands(): array
    {
        return [
            'commands' => [
                'neutrino:database:create' => Command\CreateDatabaseCommand::class,
                'neutrino:database:drop'   => Command\DropDatabaseCommand::class,
                'neutrino:migrate:tenants' => Command\MigrateTenantsCommand::class,
            ]
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array<string,mixed>
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                // Theme overrides FIRST
                'sandbox' => [
                    __DIR__ . '/../templates/sandbox',
                ],
                'layout' => [
                    __DIR__ . '/../templates/sandbox/layout', // theme override first
                    __DIR__ . '/../templates/default/layout', // theme override first
                    __DIR__ . '/../templates/layout',                // fallback
                ],
                'neutrino' => [
                    __DIR__ . '/../templates/neutrino',
                ],
                'error' => [__DIR__ . '/../templates/error'],
            ],
        ];
    }

    /**
     * Returns the Doctrine entities configuration
     * @return array<string,mixed>
     */
    public function getDoctrineEntities(): array
    {
        return [
            'driver'   => [
                'app_entity'  => [
                    'class' => AttributeDriver::class,
                    'paths' => [__DIR__ . '/Entity'],
                ],
                'orm_default' => [
                    'drivers' => [
                        'App\Entity' => 'app_entity',
                    ],
                ],
            ],
            'fixtures' => [
                'paths' => [__DIR__ . '/Fixtures'],
            ],
        ];
    }
}
