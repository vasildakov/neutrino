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

namespace Neutrino\Authentication\Handler\Google;

use League\OAuth2\Client\Provider\Google;
use Neutrino\Authentication\Resolver\RedirectResolver;
use Neutrino\Authentication\Resolver\UserResolverInterface;
use Neutrino\Log\ApplicationLoggerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class GoogleCallbackHandlerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GoogleCallbackHandler
    {
        return new GoogleCallbackHandler(
            provider: $container->get(Google::class),
            logger: $container->get(ApplicationLoggerInterface::class),
            userResolver: $container->get(UserResolverInterface::class),
            redirectResolver: $container->get(RedirectResolver::class),
        );
    }
}
