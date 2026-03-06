<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Twitter;

use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Smolblog\OAuth2\Client\Provider\Twitter;

final class TwitterProviderFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AbstractProvider
    {
        $config = $container->get('config')['oauth']['twitter'] ?? [];

        return new Twitter([
            'clientId'     => (string) ($config['client_id'] ?? ''),
            'clientSecret' => (string) ($config['client_secret'] ?? ''),
            'redirectUri'  => (string) ($config['redirect_uri'] ?? ''),
        ]);
    }
}