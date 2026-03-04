<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Google;

use League\OAuth2\Client\Provider\Google;
use Neutrino\Log\ApplicationLoggerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

final class GoogleCallbackHandlerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GoogleCallbackHandler
    {
        return new GoogleCallbackHandler(
            provider: $container->get(Google::class),
            logger: $container->get(ApplicationLoggerInterface::class),
            userResolver: $container->get(GoogleUserResolverInterface::class),
        );
    }
}

