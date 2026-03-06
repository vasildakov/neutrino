<?php

declare(strict_types=1);

namespace Neutrino\Middleware;

use Laminas\Translator\TranslatorInterface;
use Psr\Container\ContainerInterface;

final class SetLocaleMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): SetLocaleMiddleware
    {
//        $config = $container->has('config') ? $container->get('config') : [];
//
//        return new SetLocaleMiddleware(
//            $container->get(UrlHelper::class),
//            $config['i18n']['default_locale'] ?? null
//        );

        return new SetLocaleMiddleware(
            $container->get(TranslatorInterface::class),
            'en_US' // fallback locale
        );
    }
}
