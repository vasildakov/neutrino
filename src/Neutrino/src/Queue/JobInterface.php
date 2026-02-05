<?php

declare(strict_types=1);

namespace Neutrino\Queue;

interface JobInterface
{
    public function getId(): string;

    public function getPayload(): array;
}
