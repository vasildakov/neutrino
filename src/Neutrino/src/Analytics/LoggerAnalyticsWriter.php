<?php

declare(strict_types=1);

namespace Neutrino\Analytics;

use Neutrino\Analytics\Event\AnalyticsEvent;
use Neutrino\Analytics\Writer\AnalyticsWriterInterface;
use Psr\Log\LoggerInterface;

use const DATE_ATOM;

final readonly class LoggerAnalyticsWriter implements AnalyticsWriterInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function write(AnalyticsEvent $event): void
    {
        $this->logger->info('analytics.event', [
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
            'device'         => $event->device,
            'os'             => $event->os,
            'osVersion'      => $event->osVersion,
            'browser'        => $event->browser,
            'browserVersion' => $event->browserVersion,
        ]);
    }
}
