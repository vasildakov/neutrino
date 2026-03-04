<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Google;

use League\OAuth2\Client\Provider\Google;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GoogleProviderFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Google
    {
        $config = $container->get('config')['google_oauth'] ?? [];

        return new Google([
            'clientId'     => (string) ($config['client_id'] ?? ''),
            'clientSecret' => (string) ($config['client_secret'] ?? ''),
            'redirectUri'  => (string) ($config['redirect_uri'] ?? ''),
            // 'hostedDomain' => 'example.com', // optional: restrict to GSuite domain
        ]);
    }
}
