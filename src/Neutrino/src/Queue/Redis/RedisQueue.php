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

namespace Neutrino\Queue\Redis;

use InvalidArgumentException;
use JsonException;
use Neutrino\Queue\JobInterface;
use Neutrino\Queue\QueueInterface;
use Redis;

use function json_decode;
use function json_encode;
use function time;
use function uniqid;

use const JSON_THROW_ON_ERROR;

/**
 * Redis Queue Implementation
 *
 * Uses Redis lists for queue management:
 * - RPUSH to add jobs to the end of the queue
 * - BLPOP to block and wait for jobs from the beginning
 * - Separate processing list to track jobs being processed
 */
final class RedisQueue implements QueueInterface
{
    private const string QUEUE_PREFIX      = 'queue:';
    private const string PROCESSING_PREFIX = 'processing:';

    public function __construct(
        private readonly Redis $redis,
        private readonly string $defaultQueue = 'default'
    ) {
    }

    /**
     * Push a job onto the queue.
     *
     * @param string $queue Queue name
     * @param array<string, mixed> $payload Job payload
     * @throws JsonException
     */
    public function push(string $queue, array $payload): void
    {
        $queueKey = self::QUEUE_PREFIX . $queue;
        $jobId    = uniqid('job_', true);

        $data = json_encode([
            'id'         => $jobId,
            'payload'    => $payload,
            'created_at' => time(),
        ], JSON_THROW_ON_ERROR);

        $this->redis->rPush($queueKey, $data);
    }

    /**
     * Pop (reserve) a job from the queue.
     *
     * @param string $queue Queue name
     * @param int $timeout Timeout in seconds (0 = no timeout)
     * @throws JsonException
     */
    public function pop(string $queue, int $timeout = 1): ?JobInterface
    {
        $queueKey      = self::QUEUE_PREFIX . $queue;
        $processingKey = self::PROCESSING_PREFIX . $queue;

        // BLPOP blocks until a job is available or timeout occurs
        // Returns [queueName, data] or false if timeout
        $result = $this->redis->blPop([$queueKey], $timeout);

        if ($result === false || ! isset($result[1])) {
            return null;
        }

        $data = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);

        // Move to processing list for reliability
        $this->redis->rPush($processingKey, $result[1]);

        return new RedisJob(
            $data['id'],
            $data['payload']
        );
    }

    /**
     * Acknowledge successful processing.
     *
     * Removes the job from the processing list.
     */
    public function ack(JobInterface $job): void
    {
        if (! $job instanceof RedisJob) {
            throw new InvalidArgumentException('Expected RedisJob.');
        }

        $processingKey = self::PROCESSING_PREFIX . $this->defaultQueue;

        // Remove the job from the processing list
        // We need to find and remove it by job ID
        $length = $this->redis->lLen($processingKey);

        for ($i = 0; $i < $length; $i++) {
            $item = $this->redis->lIndex($processingKey, $i);
            if ($item === false) {
                continue;
            }

            $data = json_decode($item, true);
            if (isset($data['id']) && $data['id'] === $job->getId()) {
                // Mark for removal by setting a placeholder
                $this->redis->lSet($processingKey, $i, '__DELETED__');
                break;
            }
        }

        // Remove all placeholders
        $this->redis->lRem($processingKey, '__DELETED__', 0);
    }

    /**
     * Reject / retry job.
     *
     * @param bool $requeue If true, put back into queue; if false, discard
     */
    public function reject(JobInterface $job, bool $requeue = false): void
    {
        if (! $job instanceof RedisJob) {
            throw new InvalidArgumentException('Expected RedisJob.');
        }

        $processingKey = self::PROCESSING_PREFIX . $this->defaultQueue;
        $queueKey      = self::QUEUE_PREFIX . $this->defaultQueue;

        // Find and remove from a processing list
        $length = $this->redis->lLen($processingKey);

        for ($i = 0; $i < $length; $i++) {
            $item = $this->redis->lIndex($processingKey, $i);
            if ($item === false) {
                continue;
            }

            $data = json_decode($item, true);
            if (isset($data['id']) && $data['id'] === $job->getId()) {
                if ($requeue) {
                    // Put back to the end of the queue
                    $this->redis->rPush($queueKey, $item);
                }

                // Remove from processing
                $this->redis->lSet($processingKey, $i, '__DELETED__');
                break;
            }
        }

        // Clean up placeholders
        $this->redis->lRem($processingKey, '__DELETED__', 0);
    }

    /**
     * Get queue size
     */
    public function size(string $queue): int
    {
        $queueKey = self::QUEUE_PREFIX . $queue;
        return $this->redis->lLen($queueKey);
    }

    /**
     * Clear all jobs from queue
     */
    public function clear(string $queue): void
    {
        $queueKey = self::QUEUE_PREFIX . $queue;
        $this->redis->del($queueKey);
    }
}
