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
namespace Neutrino\Queue;

use PhpAmqpLib\Message\AMQPMessage;

final class RabbitMqJob implements JobInterface
{
    public function __construct(
        private readonly AMQPMessage $message,
        private readonly array $payload
    ) {}

    public function getId(): string
    {
        return (string) ($this->message->get('message_id') ?? '');
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getMessage(): AMQPMessage
    {
        return $this->message;
    }
}
