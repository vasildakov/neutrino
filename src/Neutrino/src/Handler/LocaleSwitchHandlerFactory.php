<?php

declare(strict_types=1);

namespace Neutrino\Handler;

use Psr\Container\ContainerInterface;
use Mezzio\Helper\UrlHelper;

class LocaleSwitchHandlerFactory
{
    public function __invoke(ContainerInterface $container): LocaleSwitchHandler
    {
        return new LocaleSwitchHandler(
            $container->get(UrlHelper::class)
        );
    }
}
