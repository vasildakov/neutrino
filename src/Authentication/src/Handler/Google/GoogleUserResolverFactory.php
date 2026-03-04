<?php

namespace Neutrino\Authentication\Handler\Google;

use Psr\Container\ContainerInterface;

final class GoogleUserResolverFactory
{
    public function __invoke(ContainerInterface $container): GoogleUserResolver
    {
        return new GoogleUserResolver();
    }
}
