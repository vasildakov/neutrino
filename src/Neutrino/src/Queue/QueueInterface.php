<?php

declare(strict_types=1);

namespace Neutrino\Queue;

interface QueueInterface
{
    /**
     * Push a job onto the queue.
     */
    public function push(string $queue, array $payload): void;

    /**
     * Pop (reserve) a job from the queue.
     * Returns null if nothing is available.
     */
    public function pop(string $queue): ?JobInterface;

    /**
     * Acknowledge successful processing.
     */
    public function ack(JobInterface $job): void;

    /**
     * Reject / retry job.
     */
    public function reject(JobInterface $job, bool $requeue = false): void;
}
