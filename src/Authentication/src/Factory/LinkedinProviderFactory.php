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

use Neutrino\Authentication\Provider\LinkedinProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class LinkedinProviderFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LinkedinProvider
    {
        $config = $container->get('config')['oauth']['linkedin'];

        return new LinkedinProvider([
            'clientId'                => $config['client_id'],
            'clientSecret'            => $config['client_secret'],
            'redirectUri'             => $config['redirect_uri'],
            'urlAuthorize'            => $config['authorize_url'],
            'urlAccessToken'          => $config['token_url'],
            'urlResourceOwnerDetails' => $config['userinfo_url'],
            'scopeSeparator'          => ' ',
        ]);
    }
}
