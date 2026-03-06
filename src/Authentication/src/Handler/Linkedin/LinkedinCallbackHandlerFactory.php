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

namespace Neutrino\Authentication\Handler\Linkedin;

use Neutrino\Authentication\Provider\LinkedinProvider;
use Neutrino\Authentication\Resolver\RedirectResolver;
use Neutrino\Authentication\Resolver\UserResolverInterface;
use Neutrino\Log\ApplicationLoggerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

final class LinkedinCallbackHandlerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LinkedinCallbackHandler
    {
        $linkedin = $container->get(LinkedinProvider::class);
        assert($linkedin instanceof LinkedinProvider);

        $logger = $container->get(ApplicationLoggerInterface::class);
        assert($logger instanceof LoggerInterface);

        $userResolver = $container->get(UserResolverInterface::class);
        assert($userResolver instanceof UserResolverInterface);

        $redirectResolver = $container->get(RedirectResolver::class);
        assert($redirectResolver instanceof RedirectResolver);

        return new LinkedinCallbackHandler($linkedin, $logger, $userResolver, $redirectResolver);
    }
}
