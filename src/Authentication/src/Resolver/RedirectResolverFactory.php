<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Resolver;

use Psr\Container\ContainerInterface;

final class RedirectResolverFactory
{
    public function __invoke(ContainerInterface $container): RedirectResolver
    {
        return new RedirectResolver();
    }
}
