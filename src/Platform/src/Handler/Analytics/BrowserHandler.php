<?php

declare(strict_types=1);

namespace Platform\Handler\Analytics;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function max;
use function round;
use function usort;

class BrowserHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $days = (int) ($request->getQueryParams()['days'] ?? 30);
        if ($days <= 0) {
            $days = 30;
        }

        // Deterministic "fake" baseline; you can later replace this with repository output.
        // Make it tenant-aware if you already have tenant id in request attributes.
        $seed = $days * 1337;

        $data = [
            ['browser' => 'Chrome',           'value' => 0],
            ['browser' => 'Safari',           'value' => 0],
            ['browser' => 'Firefox',          'value' => 0],
            ['browser' => 'Edge',             'value' => 0],
            ['browser' => 'Samsung Internet', 'value' => 0],
            ['browser' => 'Opera',            'value' => 0],
            ['browser' => 'Other',            'value' => 0],
        ];

        foreach ($data as $i => $row) {
            // Simple stable pseudo-random-ish distribution
            $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;

            $base = match ($row['browser']) {
                'Chrome'           => 12000,
                'Safari'           => 6000,
                'Firefox'          => 3000,
                'Edge'             => 2400,
                'Samsung Internet' => 900,
                'Opera'            => 550,
                'Other'            => 400,
                default            => 500,
            };

            // Add mild variation per days
            $variation = ($seed % 700) - 350; // -350..+349
            $value     = max(0, $base + (int) round($variation * ($days / 30)));

            $data[$i]['value'] = $value;
        }

        // Sort biggest first (nice for donut legends)
        usort($data, static fn(array $a, array $b) => $b['value'] <=> $a['value']);

        return new JsonResponse($data);
    }
}
