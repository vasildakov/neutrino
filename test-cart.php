<?php

declare(strict_types=1);

// Test script to debug cart functionality

use Neutrino\Domain\Billing\Plan;
use Neutrino\Service\Cart\CartService;

require __DIR__ . '/vendor/autoload.php';

try {
    echo "Loading container...\n";
    $container = require __DIR__ . '/config/container.php';
    echo "✓ Container loaded\n\n";

    // Test EntityManager
    echo "Testing EntityManager...\n";
    $em = $container->get('doctrine.entity_manager.orm_default');
    echo "✓ EntityManager loaded\n\n";

    // Test CartService
    echo "Testing CartService...\n";
    $cartService = $container->get(CartService::class);
    echo "✓ CartService loaded: " . $cartService::class . "\n\n";

    // Test getting a cart
    echo "Testing cart creation...\n";
    $cart = $cartService->getCart(null, 'test-session-123');
    echo "✓ Cart created: " . $cart->getId() . "\n";
    echo "  - Items: " . $cart->getItemCount() . "\n";
    echo "  - Total: $" . $cart->getTotalFormatted() . "\n\n";

    // Test finding a plan
    echo "Testing Plan repository...\n";
    $plans = $em->getRepository(Plan::class)->findAll();
    echo "✓ Found " . count($plans) . " plans\n";
    if (! empty($plans)) {
        $plan = $plans[0];
        echo "  - First plan: " . $plan->getName() . "\n\n";

        // Test adding plan to cart
        echo "Testing add plan to cart...\n";
        $item = $cartService->addPlan($cart, $plan, 'monthly');
        echo "✓ Plan added to cart\n";
        echo "  - Item ID: " . $item->getId() . "\n";
        echo "  - Item name: " . $item->getName() . "\n";
        echo "  - Price: $" . $item->getUnitPriceFormatted() . "\n";
        echo "  - Cart total: $" . $cart->getTotalFormatted() . "\n\n";
    }

    echo "✅ All tests passed!\n";
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
