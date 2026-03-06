<?php

declare(strict_types=1);

use Monolog\Level;
use Neutrino\Log\AnalyticsLoggerInterface;
use Neutrino\Log\ApplicationLoggerInterface;
use Neutrino\Log\LoggerFactory;

return [
    'dependencies' => [
        'factories' => [
            ApplicationLoggerInterface::class => LoggerFactory::class,
            AnalyticsLoggerInterface::class   => LoggerFactory::class,
        ],
    ],
    'logger'       => [
        'channels' => [
            ApplicationLoggerInterface::class => [
                'path'  => 'var/log/app.log',
                'level' => Level::Debug,
            ],
            AnalyticsLoggerInterface::class   => [
                'path'  => 'var/log/analytics.log',
                'level' => Level::Info,
            ],
            'billing.logger'                  => [
                'path'  => 'var/log/billing.log',
                'level' => Level::Warning,
            ],
        ],
    ],
];
