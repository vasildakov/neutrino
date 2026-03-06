<?php

declare(strict_types=1);

namespace Neutrino\Mail;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use function sprintf;

final class SendTestEmail
{
    /**
     * @param array<string, string> $mailConfig
     */
    public function __construct(
        private MailerInterface $mailer,
        private array $mailConfig,
    ) {
    }

    public function send(string $to): void
    {
        $from = $this->mailConfig['default_from'] ?? ['email' => 'no-reply@neutrino.local', 'name' => 'Neutrino'];

        $email = (new Email())
            ->from(sprintf('%s <%s>', $from['name'], $from['email']))
            ->to($to)
            ->subject('Mailpit test')
            ->text('Hello from Neutrino → Mailpit!')
            ->html('<p>Hello from <strong>Neutrino</strong> → <strong>Mailpit</strong>!</p>');

        $this->mailer->send($email);
    }
}
