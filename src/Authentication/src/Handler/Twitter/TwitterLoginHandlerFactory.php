<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Twitter;

use Psr\Container\ContainerInterface;
use Smolblog\OAuth2\Client\Provider\Twitter;

final class TwitterLoginHandlerFactory
{
    public function __invoke(ContainerInterface $container): TwitterLoginHandler
    {
        $config = $container->get('config')['twitter_oauth'] ?? [];

        return new TwitterLoginHandler(
            provider: $container->get(Twitter::class),
            scopes: (array) ($config['scopes'] ?? ['tweet.read', 'users.read']),
            successRedirectPath: (string) ($config['success_redirect'] ?? '/auth/twitter/success'),
        );
    }
}
