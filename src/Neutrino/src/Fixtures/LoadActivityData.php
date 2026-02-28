<?php

declare(strict_types=1);

/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neutrino\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Neutrino\Domain\User\User;
use Neutrino\Domain\User\UserActivity;

use function array_rand;
use function rand;

final class LoadActivityData extends AbstractFixture
{
    private const ACTIVITY_TYPES = [
        ['type' => 'login', 'desc' => 'User logged in successfully'],
        ['type' => 'logout', 'desc' => 'User logged out'],
        ['type' => 'view', 'desc' => 'Viewed dashboard'],
        ['type' => 'update', 'desc' => 'Profile information updated'],
        ['type' => 'password', 'desc' => 'Password changed'],
        ['type' => 'view', 'desc' => 'Viewed account settings'],
        ['type' => 'update', 'desc' => 'Updated account settings'],
    ];

    private const BROWSERS = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edge/131.0.0.0',
    ];

    private const CITIES = ['Sliven', 'Sofia', 'Plovdiv', 'Varna', 'Burgas'];

    private const IP_ADDRESSES = ['192.168.1.100', '10.0.0.50', '172.16.0.25', '192.168.0.200'];

    public function load(ObjectManager $manager): void
    {
        // Find all users
        $users = $manager->getRepository(User::class)->findAll();

        if (empty($users)) {
            return; // Skip if no users exist
        }

        foreach ($users as $user) {
            // Create 5-10 random activities per user
            $numActivities = rand(5, 10);

            for ($i = 0; $i < $numActivities; $i++) {
                $activityData = self::ACTIVITY_TYPES[array_rand(self::ACTIVITY_TYPES)];

                $activity = new UserActivity(
                    user: $user,
                    activityType: $activityData['type'],
                    description: $activityData['desc'],
                    ipAddress: self::IP_ADDRESSES[array_rand(self::IP_ADDRESSES)],
                    userAgent: self::BROWSERS[array_rand(self::BROWSERS)],
                    city: self::CITIES[array_rand(self::CITIES)]
                );

                $manager->persist($activity);
            }
        }

        $manager->flush();
    }
}

