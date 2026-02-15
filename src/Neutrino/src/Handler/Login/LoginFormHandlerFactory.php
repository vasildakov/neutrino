<?php

declare(strict_types=1);
/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Neutrino\Handler\Login;

use Laminas\Session\Container;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Mail\SendTestEmail;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class LoginFormHandlerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LoginFormHandler
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $sendTestEmail = $container->get(SendTestEmail::class);
        assert($sendTestEmail instanceof SendTestEmail);

        return new LoginFormHandler($template, $sendTestEmail);
    }
}