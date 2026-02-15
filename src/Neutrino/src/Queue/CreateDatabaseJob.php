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

final class CreateDatabaseJob implements JobInterface
{
    public function __construct(
        private readonly string $id,
        private readonly CreateDatabasePayload $payload
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload->toArray();
    }
}
