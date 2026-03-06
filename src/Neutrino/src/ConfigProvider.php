<?php

declare(strict_types=1);

namespace Neutrino;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Laminas\I18n\Translator\Translator;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Neutrino\Analytics\LoggerAnalyticsWriter;
use Neutrino\Analytics\LoggerAnalyticsWriterFactory;
use Neutrino\Analytics\RedisQueueAnalyticsWriter;
use Neutrino\Analytics\RedisQueueAnalyticsWriterFactory;
use Neutrino\Analytics\Writer\AnalyticsWriterInterface;
use Neutrino\Analytics\Writer\FallbackWriter;
use Neutrino\Handler\LocaleSwitchHandler;
use Neutrino\Mail\SendTestEmail;
use Neutrino\Mail\SendTestEmailFactory;
use Neutrino\Mail\SymfonyMailerFactory;
//use Neutrino\Queue\Beanstalkd\BeanstalkdQueueFactory;
//use Neutrino\Queue\QueueInterface;
//use Neutrino\Queue\Redis\RedisQueue;
//use Neutrino\Queue\Redis\RedisQueueFactory;
use Neutrino\Middleware\StoreIntendedUrlMiddleware;
use Neutrino\Service\Payment\PaymentServiceInterface;
use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\Mailer\MailerInterface;

use function assert;

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
     *
     * @return array<string,mixed>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineEntities(),
            'templates'    => $this->getTemplates(),
            'laminas-cli'  => $this->getCommands(),
            'routes'       => $this->getRoutes(),
            'view_helpers' => $this->getViewHelperConfig(),
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
                // Translator aliases
            ],
            'invokables' => [
                Handler\PingHandler::class                        => Handler\PingHandler::class,
                Middleware\StorePostAuthRedirectMiddleware::class => Middleware\StorePostAuthRedirectMiddleware::class,
                StoreIntendedUrlMiddleware::class                 => StoreIntendedUrlMiddleware::class,
            ],
            'factories'  => [
                // Translator
                Translator::class => I18n\TranslatorFactory::class,

                // Infrastructure
                LoggerInterface::class => function (ContainerInterface $container) {
                    $logger = new Logger('analytics');
                    $logger->pushHandler(new StreamHandler('./var/log/analytics.log'));
                    return $logger;
                },

                Pheanstalk::class
                    => function (ContainerInterface $container): Pheanstalk {
                        return Pheanstalk::create('beanstalkd');
                    },
                Redis::class                                      => function (): Redis {
                    $redis = new Redis();
                    $redis->connect('redis', 6379);
                    return $redis;
                },
                MailerInterface::class                            => SymfonyMailerFactory::class,
                SendTestEmail::class                              => SendTestEmailFactory::class,
                Middleware\InjectUserToTemplatesMiddleware::class => Middleware\InjectUserToTemplatesMiddlewareFactory::class,
                Middleware\AuthorizationMiddleware::class         => Middleware\AuthorizationMiddlewareFactory::class,
                Middleware\LocalizationMiddleware::class          => Middleware\LocalizationMiddlewareFactory::class,
                LoggerAnalyticsWriter::class                      => LoggerAnalyticsWriterFactory::class,
                RedisQueueAnalyticsWriter::class                  => RedisQueueAnalyticsWriterFactory::class,
                AnalyticsWriterInterface::class                   => function ($container): FallbackWriter {
                    $primary = $container->get(RedisQueueAnalyticsWriter::class);
                    assert($primary instanceof RedisQueueAnalyticsWriter);

                    $fallback = $container->get(LoggerAnalyticsWriter::class);
                    assert($fallback instanceof LoggerAnalyticsWriter);

                    return new FallbackWriter(primary: $primary, fallback: $fallback);
                },

                //SessionManager::class => Session\SessionManagerFactory::class,
                //Container::class => Session\SessionContainerFactory::class,

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
                // Services
                Service\Cart\CartService::class => Service\Cart\CartServiceFactory::class,
                Service\ConsentService::class   => Service\ConsentServiceFactory::class,

                // Redirect to locale
                LocaleSwitchHandler::class     => Handler\LocaleSwitchHandlerFactory::class,
                Handler\RedirectHandler::class => Handler\RedirectHandlerFactory::class,

                // Forms
                Handler\Checkout\CheckoutForm::class => Handler\Checkout\CheckoutFormFactory::class,

                // Handlers
                Handler\Home\HomePageHandler::class            => Handler\Home\HomePageHandlerFactory::class,
                Handler\Checkout\CheckoutFormHandler::class    => Handler\Checkout\CheckoutFormHandlerFactory::class,
                Handler\Checkout\CheckoutProcessHandler::class => Handler\Checkout\CheckoutProcessHandlerFactory::class,
                Handler\Checkout\FakePaymentHandler::class     => Handler\Checkout\FakePaymentHandlerFactory::class,
                Handler\Checkout\CheckoutReturnHandler::class  => Handler\Checkout\CheckoutReturnHandlerFactory::class,
                Handler\Checkout\CheckoutCancelHandler::class  => Handler\Checkout\CheckoutCancelHandlerFactory::class,
                Handler\Checkout\CheckoutSuccessHandler::class => Handler\Checkout\CheckoutSuccessHandlerFactory::class,

                // Cart Handlers
                Handler\Cart\ViewCartHandler::class       => Handler\Cart\ViewCartHandlerFactory::class,
                Handler\Cart\AddToCartHandler::class      => Handler\Cart\AddToCartHandlerFactory::class,
                Handler\Cart\RemoveFromCartHandler::class => Handler\Cart\RemoveFromCartHandlerFactory::class,

                // Consent Handlers
                Handler\Consent\ConsentConfigHandler::class => Handler\Consent\ConsentConfigHandlerFactory::class,
                Handler\Consent\ConsentSaveHandler::class   => Handler\Consent\ConsentSaveHandlerFactory::class,
                Handler\Consent\ConsentRevokeHandler::class => Handler\Consent\ConsentRevokeHandlerFactory::class,

                // Auth Handlers
                Handler\Login\LoginFormHandler::class       => Handler\Login\LoginFormHandlerFactory::class,
                Handler\Login\LoginHandler::class           => Handler\Login\LoginHandlerFactory::class,
                Handler\Logout\LogoutHandler::class         => Handler\Logout\LogoutHandlerFactory::class,
                Handler\Register\RegisterHandler::class     => Handler\Register\RegisterHandlerFactory::class,
                Handler\Register\RegisterFormHandler::class => Handler\Register\RegisterFormHandlerFactory::class,
                Handler\Register\RegisterService::class
                    => function (ContainerInterface $container): Handler\Register\RegisterService {
                        return new Handler\Register\RegisterService(
                            $container->get(EntityManagerInterface::class)
                        );
                    },

                // Security
                Security\Authorization\AclProviderInterface::class          => Security\Authorization\InMemoryAclProviderFactory::class,
                Security\Authorization\AuthorizationServiceInterface::class => Security\Authorization\AuthorizationServiceFactory::class,
                PaymentServiceInterface::class                              => Service\Payment\PaymentServiceFactory::class,
                // Services
                Service\Migrator::class              => Service\MigratorFactory::class,
                Tenant\TenantMigrationsRunner::class => Tenant\TenantMigrationsRunnerFactory::class,
                Tenant\TenantEntityManagerFactory::class
                    => function (ContainerInterface $container): Tenant\TenantEntityManagerFactory {
                        return new Tenant\TenantEntityManagerFactory([], true);
                    },

                // Commands
                Command\CreateDatabaseCommand::class  => Command\CreateDatabaseCommandFactory::class,
                Command\DropDatabaseCommand::class    => Command\DropDatabaseCommandFactory::class,
                Command\MigrateTenantsCommand::class  => Command\MigrateTenantsCommandFactory::class,
                Command\AnalyticsWorkerCommand::class => Command\AnalyticsWorkerCommandFactory::class,
                Repository\DatabaseRepository::class
                    => function (ContainerInterface $container): Repository\DatabaseRepository {
                        $em       = $container->get(EntityManagerInterface::class);
                        $metadata = $em->getClassMetadata(Entity\Database::class);
                        return new Repository\DatabaseRepository($em, $metadata);
                    },
            ],
        ];
    }

    /**
     * Returns the route configuration
     *
     * @return list<array<string, mixed>>
     */
    public function getRoutes(): array
    {
        return [
            [
                'name'            => 'home.redirect',
                'path'            => '/',
                'middleware'      => [Handler\RedirectHandler::class],
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'locale.switch',
                'path'            => '/locale/:locale',
                'middleware'      => LocaleSwitchHandler::class,
                'allowed_methods' => ['GET'],
                'options'         => [
                    'constraints' => [
                        'locale' => '(en|bg)',
                    ],
                    'defaults'    => [
                        'locale' => 'bg',
                    ],
                ],
            ],
            [
                'name'            => 'home',
                'path'            => '/:locale',
                'middleware'      => [Handler\Home\HomePageHandler::class],
                'allowed_methods' => ['GET'],
                'options'         => [
                    'constraints' => [
                        'locale' => '(en|bg)',
                    ],
                    'defaults'    => [
                        'locale' => 'bg',
                    ],
                ],
            ],
            [
                'name'            => 'login.form',
                'path'            => '/login',
                'middleware'      => [
                    BodyParamsMiddleware::class,
                    CsrfMiddleware::class,
                    Handler\Login\LoginFormHandler::class,
                ],
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'login.submit',
                'path'            => '/login',
                'middleware'      => [
                    BodyParamsMiddleware::class,
                    CsrfMiddleware::class,
                    Handler\Login\LoginHandler::class,
                ],
                'allowed_methods' => ['POST'],
            ],
            [
                'name'            => 'logout',
                'path'            => '/logout',
                'middleware'      => [
                    Handler\Logout\LogoutHandler::class,
                ],
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'register.form',
                'path'            => '/register',
                'middleware'      => [
                    BodyParamsMiddleware::class,
                    Handler\Register\RegisterFormHandler::class,
                ],
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'register.submit',
                'path'            => '/register',
                'middleware'      => [
                    BodyParamsMiddleware::class,
                    Handler\Register\RegisterFormHandler::class,
                ],
                'allowed_methods' => ['POST'],
            ],
            [
                'name'            => 'cart.view',
                'path'            => '/:locale/cart',
                'middleware'      => Handler\Cart\ViewCartHandler::class,
                'allowed_methods' => ['GET'],
                'options'         => [
                    'constraints' => [
                        'locale' => '(en|bg)',
                    ],
                ],
            ],
            [
                'name'            => 'cart.add',
                'path'            => '/cart/add',
                'middleware'      => [
                    Handler\Cart\AddToCartHandler::class,
                ],
                'allowed_methods' => ['POST'],
                'options'         => [
                    'constraints' => [
                        'locale' => '(en|bg)',
                    ],
                ],
            ],
            [
                'name'            => 'cart.remove',
                'path'            => '/cart/remove',
                'middleware'      => Handler\Cart\RemoveFromCartHandler::class,
                'allowed_methods' => ['POST'],
                'options'         => [
                    'constraints' => [
                        'locale' => '(en|bg)',
                    ],
                ],
            ],
            [
                'name'            => 'checkout.form',
                'path'            => '/:locale/checkout',
                'middleware'      => [
                    CsrfMiddleware::class,
                    StoreIntendedUrlMiddleware::class,
                    Handler\Checkout\CheckoutFormHandler::class,
                ],
                'allowed_methods' => ['GET'],
                'options'         => [
                    'constraints' => [
                        'locale' => '(en|bg)',
                    ],
                ],
            ],
            [
                'name'            => 'checkout.process',
                'path'            => '/:locale/checkout/process',
                'middleware'      => [
                    CsrfMiddleware::class,
                    Handler\Checkout\CheckoutProcessHandler::class,
                ],
                'allowed_methods' => ['POST'],
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
                'neutrino:database:create'  => Command\CreateDatabaseCommand::class,
                'neutrino:database:drop'    => Command\DropDatabaseCommand::class,
                'neutrino:migrate:tenants'  => Command\MigrateTenantsCommand::class,
                'neutrino:worker:analytics' => Command\AnalyticsWorkerCommand::class,
            ],
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
                'sandbox'  => [
                    __DIR__ . '/../templates/sandbox',
                ],
                'layout'   => [
                    __DIR__ . '/../templates/sandbox/layout', // theme override first
                    __DIR__ . '/../templates/default/layout', // theme override first
                    __DIR__ . '/../templates/layout', // fallback
                ],
                'neutrino' => [
                    __DIR__ . '/../templates/neutrino',
                ],
                'checkout' => [
                    __DIR__ . '/../templates/checkout',
                ],
                'error'    => [__DIR__ . '/../templates/error'],
            ],
        ];
    }

    /**
     * Returns the Doctrine entities configuration
     *
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

    /**
     * @return array<string,mixed>
     */
    public function getViewHelperConfig(): array
    {
        return [
            'factories' => [
                View\Helper\Avatar::class    => View\Helper\AvatarFactory::class,
                View\Helper\Translate::class => View\Helper\TranslateFactory::class,
            ],
            'aliases'   => [
                'avatar'    => View\Helper\Avatar::class,
                'translate' => View\Helper\Translate::class,
            ],
        ];
    }
}
