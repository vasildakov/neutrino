<?php

declare(strict_types=1);

/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 */

namespace Neutrino\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Neutrino\Domain\User\User;
use Neutrino\Domain\Activity\Activity;

use function array_rand;
use function rand;

final class LoadActivityData extends AbstractFixture
{
    private const ACTIONS = [
        ['action' => 'login', 'desc' => 'User logged in successfully'],
        ['action' => 'logout', 'desc' => 'User logged out'],
        ['action' => 'view', 'desc' => 'Viewed dashboard'],
        ['action' => 'update', 'desc' => 'Profile information updated'],
        ['action' => 'password', 'desc' => 'Password changed'],
        ['action' => 'view', 'desc' => 'Viewed account settings'],
        ['action' => 'update', 'desc' => 'Updated account settings'],
    ];

    private const USER_AGENTS = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edge/131.0.0.0',
    ];

    private const DEVICES = [
        'desktop',
        'mobile',
        'tablet',
    ];

    private const OS = [
        'macOS',
        'Windows',
        'Linux',
        'iOS',
        'Android',
    ];

    private const COUNTRIES = [
        'Bulgaria',
        'Germany',
        'United Kingdom',
        'France',
    ];

    private const CITIES = [
        'Sliven',
        'Sofia',
        'Plovdiv',
        'Varna',
        'Burgas',
    ];

    private const IP_ADDRESSES = [
        '192.168.1.100',
        '10.0.0.50',
        '172.16.0.25',
        '192.168.0.200',
    ];

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();

        if ($users === []) {
            return;
        }

        foreach ($users as $user) {

            $numActivities = rand(5, 10);

            for ($i = 0; $i < $numActivities; $i++) {

                $data = self::ACTIONS[array_rand(self::ACTIONS)];

                $activity = new Activity(
                    user: $user,
                    action: $data['action'],
                    description: $data['desc'],
                    ip: self::IP_ADDRESSES[array_rand(self::IP_ADDRESSES)],
                    userAgent: self::USER_AGENTS[array_rand(self::USER_AGENTS)],
                    os: self::OS[array_rand(self::OS)],
                    device: self::DEVICES[array_rand(self::DEVICES)],
                    country: self::COUNTRIES[array_rand(self::COUNTRIES)],
                    city: self::CITIES[array_rand(self::CITIES)],
                );

                $manager->persist($activity);
            }
        }

        $manager->flush();
    }
}
