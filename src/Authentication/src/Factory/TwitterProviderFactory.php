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

namespace Neutrino\Authentication\Factory;

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
