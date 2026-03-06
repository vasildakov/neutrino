<?php

declare(strict_types=1);

namespace Neutrino\Session;

use Laminas\Session\Container;
use Laminas\Session\SessionManager;
use Psr\Container\ContainerInterface;

class SessionManagerFactory
{
    public function __invoke(ContainerInterface $container): SessionManager
    {
        // Use the existing PHP session started by PhpSessionPersistence
        $sessionManager = new SessionManager();

        Container::setDefaultManager($sessionManager);

        return $sessionManager;
    }
}
