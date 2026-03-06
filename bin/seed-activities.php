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

use Doctrine\ORM\EntityManagerInterface;
use Neutrino\Domain\User\User;
use Neutrino\Domain\User\UserActivity;

require __DIR__ . '/../vendor/autoload.php';

/** @var Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

/** @var EntityManagerInterface $em */
$em = $container->get(EntityManagerInterface::class);

// Find all users
$users = $em->getRepository(User::class)->findAll();

if (empty($users)) {
    echo "No users found. Please create a user first.\n";
    exit(1);
}

$activityTypes = [
    ['type' => 'login', 'desc' => 'User logged in successfully'],
    ['type' => 'logout', 'desc' => 'User logged out'],
    ['type' => 'view', 'desc' => 'Viewed dashboard'],
    ['type' => 'update', 'desc' => 'Profile information updated'],
    ['type' => 'password', 'desc' => 'Password changed'],
    ['type' => 'view', 'desc' => 'Viewed account settings'],
    ['type' => 'update', 'desc' => 'Updated account settings'],
];

$browsers = [
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edge/131.0.0.0',
];

$cities = ['Sliven', 'Sofia', 'Plovdiv', 'Varna', 'Burgas'];
$ipAddresses = ['192.168.1.100', '10.0.0.50', '172.16.0.25', '192.168.0.200'];

echo "Seeding user activities...\n";

foreach ($users as $user) {
    echo "Creating activities for user: {$user->getEmail()}\n";

    // Create 5-10 random activities per user
    $numActivities = rand(5, 10);

    for ($i = 0; $i < $numActivities; $i++) {
        $activityData = $activityTypes[array_rand($activityTypes)];

        $activity = new UserActivity(
            user: $user,
            activityType: $activityData['type'],
            description: $activityData['desc'],
            ipAddress: $ipAddresses[array_rand($ipAddresses)],
            userAgent: $browsers[array_rand($browsers)],
            city: $cities[array_rand($cities)]
        );

        $em->persist($activity);
    }
}

$em->flush();

echo "✓ User activities seeded successfully!\n";

