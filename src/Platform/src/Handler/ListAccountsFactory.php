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
namespace Platform\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class ListAccountsFactory
{
    public function __invoke(ContainerInterface $container): ListAccounts
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $em = $container->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);

        return new ListAccounts($template, $em);
    }
}
