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

namespace Neutrino\Queue\Beanstalkd;

use Neutrino\Queue\JobInterface;
use Pheanstalk\Values\Job as PheanstalkJob;

final readonly class BeanstalkdJob implements JobInterface
{
    public function __construct(
        private PheanstalkJob $job,
        private array $payload
    ) {
    }

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
