<?php

namespace Neutrino\View\Helper;

use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class AvatarFactory
{
    public function __invoke(ContainerInterface $container): Avatar
    {
        return new Avatar();
    }
}