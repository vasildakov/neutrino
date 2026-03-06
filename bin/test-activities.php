<?php

declare(strict_types=1);
/*
 * Test script to verify UserActivity system is working correctly
 */

use Doctrine\ORM\EntityManagerInterface;
use Neutrino\Domain\User\User;
use Neutrino\Repository\UserActivityRepository;

require __DIR__ . '/../vendor/autoload.php';

/** @var Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

echo "Testing UserActivity System...\n";
echo str_repeat('=', 50) . "\n\n";

// Test 1: Verify EntityManagerInterface can be resolved
try {
    $em = $container->get(EntityManagerInterface::class);
    echo "✓ EntityManagerInterface resolved successfully\n";
} catch (Exception $e) {
    echo "✗ Failed to resolve EntityManagerInterface: {$e->getMessage()}\n";
    exit(1);
}

// Test 2: Verify UserActivityRepository can be resolved
try {
    $repo = $container->get(UserActivityRepository::class);
    echo "✓ UserActivityRepository resolved successfully\n";
} catch (Exception $e) {
    echo "✗ Failed to resolve UserActivityRepository: {$e->getMessage()}\n";
    exit(1);
}

// Test 3: Find a user and check their activities
try {
    $users = $em->getRepository(User::class)->findAll();
    if (empty($users)) {
        echo "⚠ No users found in database\n";
    } else {
        $user = $users[0];
        echo "✓ Found user: {$user->getEmail()}\n";

        $activities = $repo->findRecentByUser($user, 5);
        echo "✓ Found " . count($activities) . " activities for user\n";

        if (!empty($activities)) {
            echo "\nRecent Activities:\n";
            echo str_repeat('-', 50) . "\n";
            foreach ($activities as $activity) {
                echo sprintf(
                    "  - [%s] %s (%s)\n",
                    $activity->getActivityType(),
                    $activity->getDescription(),
                    $activity->getCreatedAt()->format('Y-m-d H:i:s')
                );
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Error querying activities: {$e->getMessage()}\n";
    exit(1);
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "✓ All tests passed!\n";

