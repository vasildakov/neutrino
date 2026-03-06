<?php

declare(strict_types=1);

namespace Neutrino\Handler;

use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

class RedirectHandlerFactory
{
    public function __invoke(ContainerInterface $container): RedirectHandler
    {
        return new RedirectHandler(
            $container->get(UrlHelper::class)
        );
    }
}
