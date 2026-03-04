<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Google;

use League\OAuth2\Client\Provider\Google;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class GoogleLoginHandlerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GoogleLoginHandler
    {
        $config = $container->get('config')['google_oauth'] ?? [];

        return new GoogleLoginHandler(
            provider: $container->get(Google::class),
            scopes: (array) ($config['scopes'] ?? ['openid', 'email', 'profile']),
            successRedirectPath: 'https://neutrino.dev:8443/auth/google/callback',
        );
    }
}
