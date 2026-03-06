<?php

declare(strict_types=1);

namespace Platform\Handler\Analytics;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function max;
use function min;
use function round;
use function sin;

use const M_PI;

final class VisitsHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Optional query param: ?months=12
        $months = (int) ($request->getQueryParams()['months'] ?? 12);
        $months = $months > 0 ? min($months, 36) : 12;

        // Anchor: 1st day of current month at 00:00 (Europe/Sofia)
        $tz     = new DateTimeZone('Europe/Sofia');
        $now    = new DateTimeImmutable('now', $tz);
        $anchor = $now->setDate((int) $now->format('Y'), (int) $now->format('m'), 1)
            ->setTime(0, 0, 0);

        // Deterministic PRNG (stable)
        $seed = 98765;

        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            // First day of each month
            $d = $anchor->sub(new DateInterval('P' . $i . 'M'));

            $month = (int) $d->format('n'); // 1..12

            // Seasonality (approx): 120 * sin((month/12)*2π)
            $season = (int) round(120 * sin(($month / 12) * 2 * M_PI));

            // Spike every 5th item (similar to your JS i % 5 === 0)
            $spike = 0;
            if ($i % 5 === 0) {
                $spike = $this->randInt($seed, 250, 600);
            }

            $visits = max(
                1200,
                5200 + $this->randInt($seed, -700, 900) + $season + $spike
            );

            // ~4.5% conversion with small randomness: 0.045 + rand(-10..10)/1000
            $rate          = 0.18 + ($this->randInt($seed, -20, 20) / 1000); // ~16%–20%
            $registrations = max(40, (int) round($visits * $rate));

            $data[] = [
                'date'          => $d->getTimestamp() * 1000,
                'visits'        => $visits,
                'registrations' => $registrations,
            ];
        }

        return new JsonResponse($data);
    }

    private function randInt(int &$seed, int $min, int $max): int
    {
        // LCG: same constants as your JS example
        $seed = (int) (($seed * 1664525 + 1013904223) % 4294967296);

        $range = $max - $min + 1;
        $value = $seed % $range;

        return $min + $value;
    }
}
