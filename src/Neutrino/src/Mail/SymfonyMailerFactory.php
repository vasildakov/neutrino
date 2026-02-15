<?php

namespace Neutrino\Mail;

use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

final class SymfonyMailerFactory
{
    public function __invoke(ContainerInterface $container): MailerInterface
    {
        $config = $container->get('config')['mail']['smtp'] ?? [];

        $host = $config['host'] ?? 'mailpit';
        $port = $config['port'] ?? 1025;

        // Mailpit: no TLS, no auth
        $dsn = sprintf('smtp://%s:%d', $host, $port);

        $transport = Transport::fromDsn($dsn);

        return new Mailer($transport);
    }
}
