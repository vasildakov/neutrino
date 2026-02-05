<?php

declare(strict_types=1);

namespace Neutrino\Queue;

use Pheanstalk\Values\Job as PheanstalkJob;

final class BeanstalkdJob implements JobInterface
{
    public function __construct(
        private readonly PheanstalkJob $job,
        private readonly array $payload
    ) {}

    public function getId(): string
    {
        return (string) $this->job->getId();
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function nativeJob(): PheanstalkJob
    {
        return $this->job;
    }
}
