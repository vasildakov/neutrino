<?php

declare(strict_types=1);

namespace Neutrino\Domain\Analytics;

use function str_contains;
use function strtolower;
use function trim;

final class UserAgentParser
{
    public static function browser(?string $ua): ?string
    {
        $ua = strtolower(trim((string) $ua));
        if ($ua === '') {
            return null;
        }

        // order matters (Edge contains "chrome", etc.)
        return match (true) {
            str_contains($ua, 'edg/') || str_contains($ua, 'edge') => 'Edge',
            str_contains($ua, 'opr/') || str_contains($ua, 'opera') => 'Opera',
            str_contains($ua, 'chrome/') && ! str_contains($ua, 'chromium') => 'Chrome',
            str_contains($ua, 'firefox/') => 'Firefox',
            str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome/') => 'Safari',
            str_contains($ua, 'msie') || str_contains($ua, 'trident/') => 'IE',
            str_contains($ua, 'curl') => 'curl',
            str_contains($ua, 'postmanruntime') => 'Postman',
            default => 'Other',
        };
    }
}
