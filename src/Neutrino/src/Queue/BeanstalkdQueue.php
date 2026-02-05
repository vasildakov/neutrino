<?php

declare(strict_types=1);

namespace Neutrino\Queue;

use JsonException;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\Job as PheanstalkJob;
use Pheanstalk\Values\TubeName;

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

    private function native(JobInterface $job): \Pheanstalk\Values\Job
    {
        if (! $job instanceof BeanstalkdJob) {
            throw new \InvalidArgumentException('Expected BeanstalkdJob.');
        }

        return $job->nativeJob();
    }
}
