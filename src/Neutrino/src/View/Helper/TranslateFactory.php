<?php

declare(strict_types=1);

namespace Neutrino\View\Helper;

use Laminas\I18n\Translator\TranslatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class TranslateFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Translate
    {
        return new Translate(
            $container->get(TranslatorInterface::class)
        );
    }
}

