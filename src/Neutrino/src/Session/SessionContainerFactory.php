<?php

declare(strict_types=1);

namespace Neutrino\Session;

use Laminas\Session\Container;
use Laminas\Session\SessionManager;
use Psr\Container\ContainerInterface;

class SessionContainerFactory
{
    public function __invoke(ContainerInterface $container): Container
    {
        return new Container(
            'app',
            $container->get(SessionManager::class)
        );
    }
}
