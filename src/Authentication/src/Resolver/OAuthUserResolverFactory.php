<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Resolver;

use Neutrino\Domain\User\UserRepositoryInterface;
use Neutrino\Repository\UserRepository;
use Psr\Container\ContainerInterface;

final class OAuthUserResolverFactory
{
    public function __invoke(ContainerInterface $container): OAuthUserResolver
    {
        $users = $container->get(UserRepository::class);
        return new OAuthUserResolver($users);
    }
}
