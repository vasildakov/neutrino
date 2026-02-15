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

namespace Neutrino\Service\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Neutrino\Domain\Billing\Plan;
use Neutrino\Domain\Cart\Cart;
use Neutrino\Domain\Cart\CartItem;
use Neutrino\Domain\User\User;
use Ramsey\Uuid\UuidInterface;

use function bin2hex;
use function random_bytes;

/**
 * Cart Service - Manages shopping cart operations
 */
class CartService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * Get or create cart for user or session
     */
    public function getCart(?User $user = null, ?string $sessionId = null): Cart
    {
        // If user is logged in, find their active cart
        if ($user) {
            $cart = $this->em->getRepository(Cart::class)->findOneBy([
                'user'   => $user,
                'status' => 'active',
            ]);

            if ($cart) {
                return $cart;
            }

            // Create new cart for user
            $cart = new Cart($user, null);
            $this->em->persist($cart);
            $this->em->flush();

            return $cart;
        }

        // For guest users, use session
        if (! $sessionId) {
            $sessionId = $this->getSessionId();
        }

        $cart = $this->em->getRepository(Cart::class)->findOneBy([
            'sessionId' => $sessionId,
            'status'    => 'active',
        ]);

        if ($cart) {
            return $cart;
        }

        // Create new cart for session
        $cart = new Cart(null, $sessionId);
        $this->em->persist($cart);
        $this->em->flush();

        return $cart;
    }

    /**
     * Add plan to cart
     */
    public function addPlan(Cart $cart, Plan $plan, string $billingPeriod = 'monthly'): CartItem
    {
        $price = $billingPeriod === 'yearly'
            ? (int) ($plan->getPriceYearly() * 100)
            : (int) ($plan->getPriceMonthly() * 100);

        $item = new CartItem(
            cart: $cart,
            itemType: 'plan',
            itemId: $plan->getId(),
            name: $plan->getName(),
            unitPrice: $price,
            quantity: 1,
            billingPeriod: $billingPeriod,
            metadata: [
                'plan_key'   => $plan->getKey(),
                'max_stores' => $plan->getMaxStores(),
                'max_users'  => $plan->getMaxUsers(),
            ]
        );

        $cart->addItem($item);
        $this->em->persist($cart);
        $this->em->flush();

        return $item;
    }

    /**
     * Add addon to cart
     */
    public function addAddon(
        Cart $cart,
        UuidInterface $addonId,
        string $name,
        int $price,
        int $quantity = 1,
        ?array $metadata = null
    ): CartItem {
        $item = new CartItem(
            cart: $cart,
            itemType: 'addon',
            itemId: $addonId,
            name: $name,
            unitPrice: $price,
            quantity: $quantity,
            metadata: $metadata
        );

        $cart->addItem($item);
        $this->em->persist($cart);
        $this->em->flush();

        return $item;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Cart $cart, UuidInterface $itemId): void
    {
        $cart->removeItemById($itemId);
        $this->em->persist($cart);
        $this->em->flush();
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(CartItem $item, int $quantity): void
    {
        $item->setQuantity($quantity);
        $item->getCart()->recalculate();
        $this->em->persist($item->getCart());
        $this->em->flush();
    }

    /**
     * Clear cart
     */
    public function clearCart(Cart $cart): void
    {
        $cart->clear();
        $this->em->persist($cart);
        $this->em->flush();
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Cart $cart, string $code): bool
    {
        // TODO: Implement coupon validation
        // For now, just apply a fixed 10% discount
        $discount = (int) ($cart->getSubtotal() * 0.10);

        $cart->applyCoupon($code, $discount);
        $this->em->persist($cart);
        $this->em->flush();

        return true;
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(Cart $cart): void
    {
        $cart->removeCoupon();
        $this->em->persist($cart);
        $this->em->flush();
    }

    /**
     * Complete cart (after successful payment)
     */
    public function completeCart(Cart $cart): void
    {
        $cart->complete();
        $this->em->persist($cart);
        $this->em->flush();
    }

    /**
     * Merge guest cart into user cart (when user logs in)
     */
    public function mergeGuestCart(User $user, string $guestSessionId): void
    {
        $guestCart = $this->em->getRepository(Cart::class)->findOneBy([
            'sessionId' => $guestSessionId,
            'status'    => 'active',
        ]);

        if (! $guestCart || $guestCart->isEmpty()) {
            return;
        }

        $userCart = $this->getCart($user);

        // Copy items from guest cart to user cart
        foreach ($guestCart->getItems() as $item) {
            $newItem = new CartItem(
                cart: $userCart,
                itemType: $item->getItemType(),
                itemId: $item->getItemId(),
                name: $item->getName(),
                unitPrice: $item->getUnitPrice(),
                quantity: $item->getQuantity(),
                billingPeriod: $item->getBillingPeriod(),
                metadata: $item->getMetadata()
            );
            $userCart->addItem($newItem);
        }

        // Mark guest cart as merged
        $guestCart->abandon();

        $this->em->persist($userCart);
        $this->em->persist($guestCart);
        $this->em->flush();
    }

    /**
     * Get session ID for guest cart
     */
    private function getSessionId(): string
    {
        // Generate a random session ID if none provided
        // This should normally be provided from the request's SessionInterface
        return bin2hex(random_bytes(16));
    }

    /**
     * Get cart item count (for badge display)
     */
    public function getCartItemCount(?User $user = null): int
    {
        $cart = $this->getCart($user);
        return $cart->getTotalQuantity();
    }
}
