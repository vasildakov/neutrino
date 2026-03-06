<?php

declare(strict_types=1);

namespace Neutrino\Middleware;

use Laminas\Translator\TranslatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class LocalizationMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LocalizationMiddleware
    {
        return new LocalizationMiddleware(
            $container->get(TranslatorInterface::class),
        );
    }
}
