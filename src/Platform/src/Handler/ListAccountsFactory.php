<?php

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
