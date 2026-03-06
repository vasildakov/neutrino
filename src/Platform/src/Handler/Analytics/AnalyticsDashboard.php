<?php

declare(strict_types=1);

namespace Platform\Handler\Analytics;

class AnalyticsDashboard
{
    public function __construct(public DashboardOverview $overview)
    {
    }
}
