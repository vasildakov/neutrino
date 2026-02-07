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
namespace Neutrino\Security\Authorization;

use Psr\Container\ContainerInterface;

final class AuthorizationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthorizationService
    {
        $acl = $container->get(AclProviderInterface::class);
        assert($acl instanceof AclProviderInterface);

        return new AuthorizationService($acl);
    }
}
