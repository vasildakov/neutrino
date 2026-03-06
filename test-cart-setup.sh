#!/bin/bash

echo "Testing cart functionality..."
echo ""

# Test 1: Check if route exists
echo "1. Testing route registration..."
docker exec neutrino_php grep -n "cart.add" config/routes.php
echo ""

# Test 2: Test add to cart with curl
echo "2. Testing add to cart endpoint..."
PLAN_ID="5109d521-062c-48f8-809b-d5b25ec3067c"

docker exec neutrino_php php -r "
require 'vendor/autoload.php';

\$container = require 'config/container.php';
\$em = \$container->get('doctrine.entity_manager.orm_default');
\$plans = \$em->getRepository('Neutrino\Domain\Billing\Plan')->findAll();
echo 'Available plans:' . PHP_EOL;
foreach (\$plans as \$plan) {
    echo '  - ' . \$plan->getName() . ' (ID: ' . \$plan->getId() . ')' . PHP_EOL;
}
"

echo ""
echo "3. Check if cart tables exist..."
docker exec neutrino_mysql mysql -uneutrino -pneutrino neutrino_core -e "SELECT COUNT(*) as count FROM carts;" 2>&1
docker exec neutrino_mysql mysql -uneutrino -pneutrino neutrino_core -e "SELECT COUNT(*) as count FROM cart_items;" 2>&1

echo ""
echo "Done!"

