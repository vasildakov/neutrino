<?php

declare(strict_types=1);

/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neutrino\Authentication\Google;

use League\OAuth2\Client\Provider\Google;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class GoogleProviderFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Google
    {
        $config = $container->get('config')['oauth']['google'] ?? [];

        return new Google([
            'clientId'     => (string) ($config['client_id'] ?? ''),
            'clientSecret' => (string) ($config['client_secret'] ?? ''),
            'redirectUri'  => (string) ($config['redirect_uri'] ?? ''),
            // 'hostedDomain' => 'example.com', // optional: restrict to GSuite domain
        ]);
    }
}
