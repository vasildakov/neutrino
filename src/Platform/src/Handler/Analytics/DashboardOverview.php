<?php

declare(strict_types=1);

namespace Platform\Handler\Analytics;

final readonly class DashboardOverview
{
    public function __construct(
        public int $totalVisits,
        public int $uniqueVisitors,
        public float $bounceRate,
        public float $averageDurationMs,
        public float $conversionRate,
    ) {
    }
}
