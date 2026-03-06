<?php

declare(strict_types=1);

// Load .env early
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dotenv\Dotenv;
use Neutrino\Middleware\SetLocaleMiddleware;
use Neutrino\Middleware\SetLocaleMiddlewareFactory;
use Neutrino\Service\Payment\PayPalService;
use Neutrino\Service\Payment\PayPalServiceFactory;

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';
Dotenv::createImmutable($root)->safeLoad();

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            // Alias EntityManager to EntityManagerInterface for backwards compatibility
            EntityManager::class => EntityManagerInterface::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            // Fully\Qualified\ClassName::class => Fully\Qualified\FactoryName::class,
            SetLocaleMiddleware::class => SetLocaleMiddlewareFactory::class,
            PayPalService::class       => PayPalServiceFactory::class,
        ],
    ],
];
