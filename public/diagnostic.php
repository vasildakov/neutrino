<?php

declare(strict_types=1);

// Diagnostic page for cart debugging

use Neutrino\Domain\Billing\Plan;
use Neutrino\Handler\Cart\AddToCartHandler;
use Neutrino\Service\Cart\CartService;

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html><head><title>Cart Diagnostic</title>';
echo '<style>body{font-family:monospace;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border:1px solid #ccc;overflow:auto;}</style>';
echo '</head><body><h1>Cart System Diagnostic</h1>';

try {
    echo '<h2>1. Container Loading</h2>';
    $container = require __DIR__ . '/../config/container.php';
    echo '<p class="success">✓ Container loaded</p>';

    echo '<h2>2. Database Connection</h2>';
    $pdo = new PDO('mysql:host=neutrino_mysql;dbname=neutrino_core', 'neutrino', 'neutrino');
    echo '<p class="success">✓ Database connected</p>';

    echo '<h2>3. Check Tables</h2>';
    $tables = $pdo->query("SHOW TABLES LIKE 'cart%'")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo '<p class="error">✗ No cart tables found!</p>';
        echo '<p class="info">Run: <code>docker exec neutrino_php vendor/bin/laminas doctrine:migrations:migrate</code></p>';
    } else {
        echo '<p class="success">✓ Cart tables found:</p><ul>';
        foreach ($tables as $table) {
            echo '<li>' . $table . '</li>';
        }
        echo '</ul>';
    }

    $billingTables = $pdo->query("SHOW TABLES LIKE 'billing%'")->fetchAll(PDO::FETCH_COLUMN);
    if (! empty($billingTables)) {
        echo '<p class="success">✓ Billing tables found:</p><ul>';
        foreach ($billingTables as $table) {
            echo '<li>' . $table . '</li>';
        }
        echo '</ul>';
    }

    echo '<h2>4. EntityManager</h2>';
    $em = $container->get('doctrine.entity_manager.orm_default');
    echo '<p class="success">✓ EntityManager loaded</p>';

    echo '<h2>5. CartService</h2>';
    $cartService = $container->get(CartService::class);
    echo '<p class="success">✓ CartService loaded: ' . $cartService::class . '</p>';

    echo '<h2>6. Test Cart Creation</h2>';
    $cart = $cartService->getCart(null, 'diagnostic-session-' . time());
    echo '<p class="success">✓ Cart created:</p>';
    echo '<ul>';
    echo '<li>ID: ' . $cart->getId() . '</li>';
    echo '<li>Items: ' . $cart->getItemCount() . '</li>';
    echo '<li>Total: $' . $cart->getTotalFormatted() . '</li>';
    echo '<li>Status: ' . $cart->getStatus() . '</li>';
    echo '</ul>';

    echo '<h2>7. Test Plan Loading</h2>';
    $plans = $em->getRepository(Plan::class)->findAll();
    echo '<p class="success">✓ Found ' . count($plans) . ' plans</p>';

    if (! empty($plans)) {
        $plan = $plans[0];
        echo '<ul>';
        echo '<li>First plan: ' . $plan->getName() . '</li>';
        echo '<li>ID: ' . $plan->getId() . '</li>';
        echo '<li>Price (monthly): $' . $plan->getPriceMonthly() . '</li>';
        echo '</ul>';

        echo '<h2>8. Test Adding Plan to Cart</h2>';
        try {
            $item = $cartService->addPlan($cart, $plan, 'monthly');
            echo '<p class="success">✓ Plan added to cart:</p>';
            echo '<ul>';
            echo '<li>Item ID: ' . $item->getId() . '</li>';
            echo '<li>Item name: ' . $item->getName() . '</li>';
            echo '<li>Price: $' . $item->getUnitPriceFormatted() . '</li>';
            echo '<li>Cart total items: ' . $cart->getItemCount() . '</li>';
            echo '<li>Cart total: $' . $cart->getTotalFormatted() . '</li>';
            echo '</ul>';
        } catch (Exception $e) {
            echo '<p class="error">✗ Error adding plan: ' . $e->getMessage() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    }

    echo '<h2>9. Test AddToCartHandler</h2>';
    try {
        $handler = $container->get(AddToCartHandler::class);
        echo '<p class="success">✓ AddToCartHandler loaded: ' . $handler::class . '</p>';
    } catch (Exception $e) {
        echo '<p class="error">✗ Error loading AddToCartHandler: ' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }

    echo '<h2 class="success">✅ All diagnostic tests passed!</h2>';
    echo '<p><a href="/">← Back to home</a></p>';
} catch (Exception $e) {
    echo '<h2 class="error">❌ Error: ' . $e->getMessage() . '</h2>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}

echo '</body></html>';
