<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Google;

use Psr\Container\ContainerInterface;

class GoogleSuccessHandlerFactory
{
    public function __invoke(ContainerInterface $container): GoogleSuccessHandler
    {
        return new GoogleSuccessHandler();
    }
}
