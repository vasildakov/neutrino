<?php

declare(strict_types=1);

namespace Neutrino\Handler\Register;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class RegisterFormHandlerFactory
{
    public function __invoke(ContainerInterface $container): RegisterFormHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        return new RegisterFormHandler($template);
    }
}