<?php

declare(strict_types=1);

namespace Neutrino\Handler\Login;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class LoginFormHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginFormHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new LoginFormHandler($template);
    }
}