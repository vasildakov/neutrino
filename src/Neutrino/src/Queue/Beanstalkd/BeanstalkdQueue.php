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

use InvalidArgumentException;
use JsonException;
use Neutrino\Queue\JobInterface;
use Neutrino\Queue\QueueInterface;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\Job as PheanstalkJob;
use Pheanstalk\Values\TubeName;

use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final class BeanstalkdQueue implements QueueInterface
{
    private TubeName $tube;

    public function __construct(
        private readonly Pheanstalk $client,
        string $queue
    ) {
        // One tube per worker instance (recommended)
        $this->tube = new TubeName($queue);

        // Setup once — pda/pheanstalk is stateful
        $this->client->watch($this->tube);
        $this->client->ignore(new TubeName('default'));
    }

    /**
     * @throws JsonException
     */
    public function push(string $queue, array $payload): void
    {
        $this->client->useTube(new TubeName($queue));
        $this->client->put(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public function pop(string $queue, int $timeout = 1): ?JobInterface
    {
        /** @var JobInterface $job */
        $job = $this->client->reserveWithTimeout($timeout);

        if (! $job instanceof PheanstalkJob) {
            return null;
        }

        return new BeanstalkdJob(
            $job->getId(),
            json_decode($job->getData(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function ack(JobInterface $job): void
    {
        $this->client->delete($this->native($job));
    }

    public function reject(JobInterface $job, bool $requeue = false): void
    {
        $nativeJob = $this->native($job);

        if ($requeue) {
            $this->client->release($nativeJob);
            return;
        }

        $this->client->bury($nativeJob);
    }

    private function native(JobInterface $job): PheanstalkJob
    {
        if (! $job instanceof BeanstalkdJob) {
            throw new InvalidArgumentException('Expected BeanstalkdJob.');
        }

        return $job->nativeJob();
    }
}
