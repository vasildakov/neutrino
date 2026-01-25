<?php

declare(strict_types=1);

namespace App;

use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use App\Handler\PingHandler;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
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
            'templates'    => $this->getTemplates(),
            'doctrine'     => $this->getDoctrineEntities(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                PingHandler::class => PingHandler::class,
            ],
            'factories'  => [
                HomePageHandler::class => HomePageHandlerFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => [__DIR__ . '/../templates/app'],
                'error'  => [__DIR__ . '/../templates/error'],
                'layout' => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }

    /**
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
