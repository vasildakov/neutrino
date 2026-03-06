<?php

declare(strict_types=1);

/**
 * Verify ShowUserHandler can be instantiated with all dependencies
 */

require __DIR__ . '/../vendor/autoload.php';

/** @var Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';

echo "Verifying ShowUserHandler Dependencies...\n";
echo str_repeat('=', 60) . "\n\n";

try {
    // Test 1: Get TemplateRendererInterface
    $template = $container->get(\Mezzio\Template\TemplateRendererInterface::class);
    echo "✓ TemplateRendererInterface resolved\n";
} catch (Exception $e) {
    echo "✗ Failed to resolve TemplateRendererInterface: {$e->getMessage()}\n";
    exit(1);
}

try {
    // Test 2: Get EntityManagerInterface
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    echo "✓ EntityManagerInterface resolved\n";
} catch (Exception $e) {
    echo "✗ Failed to resolve EntityManagerInterface: {$e->getMessage()}\n";
    exit(1);
}

try {
    // Test 3: Get UserActivityRepository
    $repo = $container->get(\Neutrino\Repository\UserActivityRepository::class);
    echo "✓ UserActivityRepository resolved\n";
} catch (Exception $e) {
    echo "✗ Failed to resolve UserActivityRepository: {$e->getMessage()}\n";
    exit(1);
}

try {
    // Test 4: Get ShowUserHandler (this will test the factory)
    $handler = $container->get(\Platform\Handler\ShowUserHandler::class);
    echo "✓ ShowUserHandler instantiated successfully\n";
} catch (Exception $e) {
    echo "✗ Failed to instantiate ShowUserHandler: {$e->getMessage()}\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✓ All handler dependencies verified successfully!\n";
echo "\nThe user profile page with activities is ready to use.\n";

