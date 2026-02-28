<?php

declare(strict_types=1);

namespace Neutrino\Analytics;

use Neutrino\Analytics\Event\AnalyticsEvent;
use Neutrino\Analytics\Writer\AnalyticsWriterInterface;
use Neutrino\Queue\Driver\Redis\RedisStreamsQueue;
use Neutrino\Queue\Envelope\Envelope;
use Neutrino\Queue\Envelope\StreamName;
use Ramsey\Uuid\Uuid;

use const DATE_ATOM;

/**
 * Used for Neutrino.bg
 */
final readonly class RedisQueueAnalyticsWriter implements AnalyticsWriterInterface
{
    public function __construct(
        private RedisStreamsQueue $queue,
        private string $queueName
    ) {
    }

    public function write(AnalyticsEvent $event): void
    {
        $envelope = new Envelope(
            id: Uuid::uuid4()->toString(),
            name: 'analytics.event',
            payload: [
                'occurredAt'     => $event->occurredAt->format(DATE_ATOM),
                'method'         => $event->method,
                'path'           => $event->path,
                'queryString'    => $event->queryString,
                'ip'             => $event->ip,
                'userAgent'      => $event->userAgent,
                'referer'        => $event->referer,
                'acceptLanguage' => $event->acceptLanguage,
                'status'         => $event->status,
                'durationMs'     => $event->durationMs,
                'visitorId'      => $event->visitorId,
                'sessionId'      => $event->sessionId,

                // optional enrichment fields if present
                'device'         => $event->device,
                'os'             => $event->os,
                'osVersion'      => $event->osVersion,
                'browser'        => $event->browser,
                'browserVersion' => $event->browserVersion,
            ],
            headers: [
                // add any routing/tenant headers if you have them at this stage
                'scope' => 'neutrino.bg',
            ],
        );

        // Your RedisQueue::push expects (string $queue, array $payload)
        $this->queue->push(
            new StreamName($this->queueName),
            $envelope
        );
    }
}
