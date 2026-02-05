<?php

declare(strict_types=1);

use Neutrino\Handler;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return static function (
    Application $app,
    MiddlewareFactory $factory,
    ContainerInterface $container
): void {
    $app->get('/', Handler\HomePageHandler::class, 'home');
    $app->get('/api/ping', Handler\PingHandler::class, 'api.ping');

    // register
    $app->get('/register', Handler\Register\RegisterFormHandler::class,'register.form');
    $app->post('/register', Handler\Register\RegisterHandler::class,'register.submit');

    // login
    $app->get('/login', Handler\Login\LoginFormHandler::class, 'login.form');
    $app->post('/login', Handler\Login\LoginHandler::class, 'login.submit');


    $app->get('/dashboard', \Dashboard\Handler\HomeHandler::class, 'dashboard.home');
    $app->get('/dashboard/databases', \Dashboard\Handler\ShowDatabasesHandler::class, 'dashboard.databases');

};
