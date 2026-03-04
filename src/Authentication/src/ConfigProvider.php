<?php

declare(strict_types=1);

namespace Neutrino\Authentication;

use League\OAuth2\Client\Provider\Google;
use Neutrino\Authentication\Google\GoogleProviderFactory;
use Neutrino\Authentication\Handler\Google\GoogleCallbackHandler;
use Neutrino\Authentication\Handler\Google\GoogleCallbackHandlerFactory;
use Neutrino\Authentication\Handler\Google\GoogleLoginHandler;
use Neutrino\Authentication\Handler\Google\GoogleLoginHandlerFactory;

use Neutrino\Authentication\Handler\Google\GoogleSuccessHandler;
use Neutrino\Authentication\Handler\Google\GoogleSuccessHandlerFactory;
use Neutrino\Authentication\Handler\Google\GoogleUserResolverFactory;
use Neutrino\Authentication\Handler\Google\GoogleUserResolverInterface;
use Neutrino\Authentication\Handler\Twitter\TwitterLoginHandler;
use Neutrino\Authentication\Handler\Twitter\TwitterLoginHandlerFactory;
use Neutrino\Authentication\Twitter\TwitterProviderFactory;
use Smolblog\OAuth2\Client\Provider\Twitter;
use function getenv;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'routes'       => $this->getRoutes(),
            'google_oauth' => $this->getGoogleConfig(),
            'twitter_oauth' => $this->getTwitterConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                Google::class                => GoogleProviderFactory::class,
                GoogleLoginHandler::class    => GoogleLoginHandlerFactory::class,
                GoogleCallbackHandler::class => GoogleCallbackHandlerFactory::class,
                GoogleSuccessHandler::class  => GoogleSuccessHandlerFactory::class,
                GoogleUserResolverInterface::class => GoogleUserResolverFactory::class,

                Twitter::class                 => TwitterProviderFactory::class,
                TwitterLoginHandler::class      => TwitterLoginHandlerFactory::class,
            ],
        ];
    }

    public function getRoutes(): array
    {
        return [
            // Google OAuth routes
            [
                'name'            => 'auth.google.start',
                'path'            => '/auth/google',
                'middleware'      => GoogleLoginHandler::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'auth.google.callback',
                'path'            => '/auth/google/callback',
                'middleware'      => GoogleCallbackHandler::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'auth.google.success',
                'path'            => '/auth/google/success',
                'middleware'      => GoogleSuccessHandler::class,
                'allowed_methods' => ['GET'],
            ],
            // Twitter OAuth routes (example)
            [
                'name'            => 'auth.twitter.start',
                'path'            => '/auth/twitter',
                'middleware'      => \Neutrino\Authentication\Handler\Twitter\TwitterLoginHandler::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'auth.twitter.callback',
                'path'            => '/auth/twitter/callback',
                'middleware'      => \Neutrino\Authentication\Handler\Twitter\TwitterCallbackHandler::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'auth.twitter.success',
                'path'            => '/auth/twitter/success',
                'middleware'      => \Neutrino\Authentication\Handler\Twitter\TwitterSuccessHandler::class,
                'allowed_methods' => ['GET'],
            ],
        ];
    }

    public function getGoogleConfig(): array
    {
        return [
            'client_id'     => getenv('GOOGLE_OAUTH_CLIENT_ID') ?: '189760822019-p0dm6hi9ivoo8c7d0nj9k0ke8tsriepn.apps.googleusercontent.com',
            'client_secret' => getenv('GOOGLE_OAUTH_CLIENT_SECRET') ?: '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',
            'redirect_uri'  => 'https://neutrino.dev:8443/auth/google/callback',
            'scopes'        => ['openid', 'email', 'profile'],
        ];
    }

    public function getTwitterConfig(): array
    {
        return [
            'twitter_oauth' => [
                'client_id' => getenv('TWITTER_OAUTH_CLIENT_ID') ?: 'Z0I3Y3BBZnJ6X1Y2dXMza0lUNzM6MTpjaQ',
                'client_secret' => getenv('TWITTER_OAUTH_CLIENT_SECRET') ?: 'ETL2cTbgAMeFVNPO2_ibAjJ5JL7RseoLD8OUWUrfDLG29EGWXm',
                'redirect_uri' => getenv('TWITTER_OAUTH_REDIRECT_URI') ?: 'https://neutrino.dev:8443/auth/twitter/callback',
                // example scopes; choose what you need:
                'scopes' => ['tweet.read', 'users.read', 'offline.access'],
            ],
        ];
    }
}
