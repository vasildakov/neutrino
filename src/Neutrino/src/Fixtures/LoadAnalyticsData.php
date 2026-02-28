<?php

declare(strict_types=1);

namespace Neutrino\Fixtures;

use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Neutrino\Domain\Analytics\AnalyticsEvent;

use function count;
use function in_array;
use function random_int;
use function sprintf;
use function str_contains;

final class LoadAnalyticsData extends AbstractFixture implements OrderedFixtureInterface
{
    private const EVENTS_COUNT = 5000;
    private const BATCH_SIZE   = 200;

    public function load(ObjectManager $manager): void
    {
        $continent = 'Europe';
        $country   = 'Bulgaria';

        // name + lat/lon (as strings, because your columns are decimal -> Doctrine returns string)
        $cities = [
            ['city' => 'Sofia',           'lat' => '42.6977082', 'lon' => '23.3218675'],
            ['city' => 'Plovdiv',         'lat' => '42.1354079', 'lon' => '24.7452904'],
            ['city' => 'Varna',           'lat' => '43.2140504', 'lon' => '27.9147333'],
            ['city' => 'Burgas',          'lat' => '42.5047926', 'lon' => '27.4626361'],
            ['city' => 'Ruse',            'lat' => '43.8355713', 'lon' => '25.9656554'],
            ['city' => 'Stara Zagora',    'lat' => '42.4257763', 'lon' => '25.6344644'],
            ['city' => 'Pleven',          'lat' => '43.4085148', 'lon' => '24.6187477'],
            ['city' => 'Sliven',          'lat' => '42.6818306', 'lon' => '26.3226343'],
            ['city' => 'Dobrich',         'lat' => '43.5717862', 'lon' => '27.8272802'],
            ['city' => 'Shumen',          'lat' => '43.2706329', 'lon' => '26.9228574'],
            ['city' => 'Pernik',          'lat' => '42.6051991', 'lon' => '23.0377916'],
            ['city' => 'Haskovo',         'lat' => '41.9333433', 'lon' => '25.5552051'],
            ['city' => 'Yambol',          'lat' => '42.4832413', 'lon' => '26.5035156'],
            ['city' => 'Blagoevgrad',     'lat' => '42.0208614', 'lon' => '23.0943356'],
            ['city' => 'Veliko Tarnovo',  'lat' => '43.0756539', 'lon' => '25.6171500'],
            ['city' => 'Vratsa',          'lat' => '43.2101806', 'lon' => '23.5529210'],
            ['city' => 'Gabrovo',         'lat' => '42.8742213', 'lon' => '25.3183398'],
            ['city' => 'Kardzhali',       'lat' => '41.6500000', 'lon' => '25.3666667'],
            ['city' => 'Montana',         'lat' => '43.4125000', 'lon' => '23.2250000'],
            ['city' => 'Targovishte',     'lat' => '43.2512000', 'lon' => '26.5722000'],
        ];

        $paths = [
            '/',
            '/login',
            '/register',
            '/pricing',
            '/dashboard',
            '/products',
            '/products/view',
            '/cart',
            '/checkout',
            '/orders',
            '/api/health',
        ];

        $methods = ['GET', 'GET', 'GET', 'POST', 'PUT', 'DELETE'];

        $userAgents = [
            // Chrome / Edge / Firefox / Safari / Mobile + some bots
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_3) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
            'Mozilla/5.0 (Linux; Android 14; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
        ];

        $acceptLanguages = [
            'bg-BG,bg;q=0.9,en;q=0.8',
            'en-GB,en;q=0.9',
            'de-DE,de;q=0.9,en;q=0.8',
        ];

        for ($i = 1; $i <= self::EVENTS_COUNT; $i++) {
            // heavier traffic for Sofia + a bit for Plovdiv/Varna
            $cityRow = $this->weightedCityPick($cities);

            $method = $methods[random_int(0, count($methods) - 1)];
            $path   = $paths[random_int(0, count($paths) - 1)];

            [$status, $durationMs] = $this->simulateOutcome($method, $path);

            $ua      = $userAgents[random_int(0, count($userAgents) - 1)];
            $browser = $this->detectBrowser($ua);

            $event = new AnalyticsEvent(
                method: $method,
                path: $path,
                status: $status,
                durationMs: $durationMs,
                queryString: $this->maybeQueryString($path),
                ip: $this->randomIpV4(),
                userAgent: $ua,
                browser: $browser,
                referer: null,
                acceptLanguage: $acceptLanguages[random_int(0, count($acceptLanguages) - 1)],
                continent: $continent,
                country: $country,
                city: $cityRow['city'],
                latitude: $cityRow['lat'],
                longitude: $cityRow['lon'],
                occurredAt: $this->randomOccurredAtLastDays(30),
            );

            $manager->persist($event);

            if ($i % self::BATCH_SIZE === 0) {
                $manager->flush();
                $manager->clear();
            }
        }

        $manager->flush();
        $manager->clear();
    }

    public function getOrder(): int
    {
        return 10;
    }

    /**
     * Sofia dominates; Plovdiv/Varna/Burgas noticeable; others smaller.
     *
     * @param array<int, array{city:string,lat:string,lon:string}> $cities
     * @return array{city:string,lat:string,lon:string}
     */
    private function weightedCityPick(array $cities): array
    {
        $roll = random_int(1, 100);

        // Sofia 35%
        if ($roll <= 35) {
            return $cities[0];
        }

        // Plovdiv 12%, Varna 10%, Burgas 8%
        if ($roll <= 47) {
            return $cities[1];
        }
        if ($roll <= 57) {
            return $cities[2];
        }
        if ($roll <= 65) {
            return $cities[3];
        }

        // rest 35% split
        return $cities[random_int(4, count($cities) - 1)];
    }

    /**
     * @return array{0:int,1:int} [status, durationMs]
     */
    private function simulateOutcome(string $method, string $path): array
    {
        $duration = random_int(5, 350);

        if (in_array($path, ['/checkout', '/orders', '/products/view'], true)) {
            $duration += random_int(80, 900);
        }

        $roll = random_int(1, 1000);
        if ($roll <= 20) {
            return [500, $duration + random_int(200, 1200)];
        }
        if ($roll <= 60) {
            return [404, $duration];
        }
        if ($roll <= 90) {
            return [403, $duration];
        }
        if ($roll <= 120) {
            return [401, $duration];
        }

        if ($path === '/login' && $method === 'GET' && random_int(1, 100) <= 25) {
            return [302, $duration];
        }

        if ($method === 'POST' && in_array($path, ['/register', '/checkout'], true)) {
            return [201, $duration + random_int(50, 400)];
        }

        return [200, $duration];
    }

    private function maybeQueryString(string $path): ?string
    {
        if ($path === '/products' && random_int(1, 100) <= 45) {
            $page = random_int(1, 10);
            $sort = ['new', 'popular', 'price_asc', 'price_desc'][random_int(0, 3)];
            return "page={$page}&sort={$sort}";
        }

        if ($path === '/' && random_int(1, 100) <= 25) {
            $utm = ['google', 'newsletter', 'facebook', 'linkedin'][random_int(0, 3)];
            return "utm_source={$utm}";
        }

        return null;
    }

    private function randomOccurredAtLastDays(int $days): DateTimeImmutable
    {
        $seconds = random_int(0, $days * 24 * 60 * 60);
        return (new DateTimeImmutable())->sub(new DateInterval('PT' . $seconds . 'S'));
    }

    private function randomIpV4(): string
    {
        return sprintf('%d.%d.%d.%d', random_int(1, 223), random_int(0, 255), random_int(0, 255), random_int(1, 254));
    }

    private function detectBrowser(string $ua): ?string
    {
        // order matters
        if (str_contains($ua, 'Googlebot')) {
            return 'Googlebot';
        }
        if (str_contains($ua, 'bingbot')) {
            return 'BingBot';
        }
        if (str_contains($ua, 'Edg/')) {
            return 'Edge';
        }
        if (str_contains($ua, 'OPR/')) {
            return 'Opera';
        }
        if (str_contains($ua, 'Brave/')) {
            return 'Brave';
        }
        if (str_contains($ua, 'SamsungBrowser')) {
            return 'Samsung Internet';
        }
        if (str_contains($ua, 'Firefox/')) {
            return 'Firefox';
        }
        if (str_contains($ua, 'Chrome/')) {
            return 'Chrome';
        }
        if (str_contains($ua, 'Safari/') && str_contains($ua, 'Version/')) {
            return 'Safari';
        }

        return 'Other';
    }
}
