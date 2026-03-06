<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\MiddlewareFactory;
use Mezzio\Session\SessionMiddleware;
use Neutrino\Handler;
use Psr\Container\ContainerInterface;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void
{
    // mezzio
    //$app->get('/', Handler\Home\HomePageHandler::class, 'home');
    //$app->get('/api/ping', Handler\PingHandler::class, 'api.ping');

    // Consent
    $app->get('/api/consent/config', Handler\Consent\ConsentConfigHandler::class, 'consent.config');
    $app->post('/api/consent/save',   Handler\Consent\ConsentSaveHandler::class,   'consent.save');
    $app->post('/api/consent/revoke', Handler\Consent\ConsentRevokeHandler::class, 'consent.revoke');




    $app->get('/checkout/fake-payment', Handler\Checkout\FakePaymentHandler::class, 'checkout.fake-payment');
    $app->get('/checkout/return', Handler\Checkout\CheckoutReturnHandler::class, 'checkout.return');
    $app->get('/checkout/cancel', Handler\Checkout\CheckoutCancelHandler::class, 'checkout.cancel');
    $app->get('/checkout/success', Handler\Checkout\CheckoutSuccessHandler::class, 'checkout.success');

    // saas platform
    //$app->get('/platform', \Platform\Handler\HomeHandler::class, 'platform.home');
    $app->get('/platform/databases', \Platform\Handler\ShowDatabasesHandler::class, 'platform.databases');
    $app->get('/platform/queues', \Platform\Handler\QueueHandler::class, 'platform.queues');
    $app->get('/platform/accounts', \Platform\Handler\ListAccounts::class, 'platform.accounts');
    $app->get('/platform/accounts/:id', \Platform\Handler\ShowUserHandler::class, 'platform.accounts.view');

    $app->get('/platform/analytics/browser', \Platform\Handler\Analytics\BrowserHandler::class, 'platform.analytics.browser');
    $app->get('/platform/analytics/visits', \Platform\Handler\Analytics\VisitsHandler::class, 'platform.analytics.visits');


};
