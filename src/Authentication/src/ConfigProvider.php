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
use Neutrino\Authentication\Resolver\OAuthUserResolver;
use Neutrino\Authentication\Resolver\OAuthUserResolverFactory;
use Neutrino\Authentication\Resolver\RedirectResolver;
use Neutrino\Authentication\Resolver\RedirectResolverFactory;
use Neutrino\Authentication\Resolver\UserResolverInterface;
use Neutrino\Authentication\Twitter\TwitterProviderFactory;
use Smolblog\OAuth2\Client\Provider\Twitter;

class ConfigProvider
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function __invoke(): array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'routes'        => $this->getRoutes(),
            'oauth'         => $this->getOAuthConfig(),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                // Google
                Google::class                => GoogleProviderFactory::class,
                GoogleLoginHandler::class    => GoogleLoginHandlerFactory::class,
                GoogleCallbackHandler::class => GoogleCallbackHandlerFactory::class,
                GoogleSuccessHandler::class  => GoogleSuccessHandlerFactory::class,

                // Twitter
                Twitter::class                 => TwitterProviderFactory::class,
                TwitterLoginHandler::class     => TwitterLoginHandlerFactory::class,

                // Resolvers
                UserResolverInterface::class => OAuthUserResolverFactory::class,
                RedirectResolver::class => RedirectResolverFactory::class,
            ],
        ];
    }

    /**
     * @return array<array, array<string, mixed>>
     */
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
                'middleware'      => Handler\Twitter\TwitterLoginHandler::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'auth.twitter.callback',
                'path'            => '/auth/twitter/callback',
                'middleware'      => Handler\Twitter\TwitterCallbackHandler::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'auth.twitter.success',
                'path'            => '/auth/twitter/success',
                'middleware'      => Handler\Twitter\TwitterSuccessHandler::class,
                'allowed_methods' => ['GET'],
            ],
            // Facebook OAuth
            [
                'name'            => 'auth.facebook.start',
                'path'            => '/auth/facebook',
                'middleware'      => Handler\Facebook\FacebookLoginHandler::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'auth.facebook.callback',
                'path'            => '/auth/facebook/callback',
                'middleware'      => Handler\Facebook\FacebookCallbackHandler::class,
                'allowed_methods' => ['GET'],
            ],
            [
                'name'            => 'auth.facebook.success',
                'path'            => '/auth/facebook/success',
                'middleware'      => Handler\Facebook\FacebookSuccessHandler::class,
                'allowed_methods' => ['GET'],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getOAuthConfig(): array
    {
        return [
            'google' => [
                'client_id'     => $_ENV['GOOGLE_OAUTH_CLIENT_ID'] ?? '',
                'client_secret' => $_ENV['GOOGLE_OAUTH_CLIENT_SECRET'] ?? '',
                'redirect_uri'  => $_ENV['GOOGLE_OAUTH_REDIRECT_URI'] ?? '',
                'scopes'        => ['openid', 'email', 'profile'],
            ],
            'twitter' => [
                'client_id'     => $_ENV['TWITTER_OAUTH_CLIENT_ID'] ?? '',
                'client_secret' => $_ENV['TWITTER_OAUTH_CLIENT_SECRET'] ?? '',
                'redirect_uri'  => $_ENV['TWITTER_OAUTH_REDIRECT_URI'] ?? '',
                // example scopes; choose what you need:
                'scopes'        => ['tweet.read', 'users.read', 'offline.access'],
            ],
            'facebook' => [],
            'linkedin' => [],
        ];
    }
}
