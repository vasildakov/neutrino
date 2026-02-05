<?php

declare(strict_types=1);

namespace Neutrino\Handler\Login;

use Mezzio\Authentication\Session\PhpSession;
use Psr\Container\ContainerInterface;

class LoginHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        /** @var PhpSession $auth */
        $auth = $container->get(PhpSession::class);

        return new LoginHandler($auth);
    }
}