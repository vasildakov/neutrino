<?php

namespace Neutrino\Session;

use Laminas\Session\Config\SessionConfig;
use Laminas\Session\SessionManager;
use Laminas\Session\Storage\SessionArrayStorage;
use Laminas\Session\Container;
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