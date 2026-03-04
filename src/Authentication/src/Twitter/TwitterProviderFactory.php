<?php

namespace Neutrino\Authentication\Twitter;

use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Container\ContainerInterface;
use Smolblog\OAuth2\Client\Provider\Twitter;

class TwitterProviderFactory
{
    public function __invoke(ContainerInterface $c): AbstractProvider
    {
        $cfg = $c->get('config')['twitter_oauth'] ?? [];

        return new Twitter([
            'clientId'     => (string) ($cfg['client_id'] ?? ''),
            'clientSecret' => (string) ($cfg['client_secret'] ?? ''),
            'redirectUri'  => (string) ($cfg['redirect_uri'] ?? ''),
        ]);
    }
}