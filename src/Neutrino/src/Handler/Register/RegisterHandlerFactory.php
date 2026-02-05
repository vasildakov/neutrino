<?php

declare(strict_types=1);

namespace Neutrino\Handler\Register;

use Mezzio\Authentication\Session\PhpSession;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class RegisterHandlerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RegisterHandler
    {
        $service = $container->get(RegisterService::class);
        assert($service instanceof RegisterService);

        /** @var PhpSession $auth */
        $auth = $container->get(PhpSession::class);
        assert($auth instanceof PhpSession);

        return new RegisterHandler($service, $auth);
    }
}