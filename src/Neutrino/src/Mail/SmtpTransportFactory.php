<?php

namespace Neutrino\Mail;


use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Psr\Container\ContainerInterface;

final class SmtpTransportFactory
{
    public function __invoke(ContainerInterface $container): Smtp
    {
        $config = $container->get('config')['mail']['transport']['options'];

        return new Smtp(new SmtpOptions($config));
    }
}
