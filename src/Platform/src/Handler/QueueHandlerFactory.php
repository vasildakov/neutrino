<?php

namespace Platform\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;

class QueueHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        $pheanstalk = $container->get(Pheanstalk::class);
        assert($pheanstalk instanceof Pheanstalk);

        return new QueueHandler($template, $pheanstalk);
    }
}