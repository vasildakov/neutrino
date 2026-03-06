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
use InvalidArgumentException;
use Neutrino\Domain\Billing\Plan;
use Neutrino\Domain\Cart\Cart;
use Neutrino\Domain\Cart\CartItem;
use Neutrino\Domain\User\User;
use Ramsey\Uuid\UuidInterface;

final readonly class CartService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Get a cart for the session (guest).
     */
    public function getCartForSession(string $sessionId): Cart
    {
        if ($sessionId === '') {
            throw new InvalidArgumentException('sessionId is required.');
        }

        $cart = $this->em->getRepository(Cart::class)->findOneBy([
            'sessionId' => $sessionId,
            'status'    => 'active',
        ]);

        if ($cart instanceof Cart) {
            return $cart;
        }

        $cart = new Cart(null, $sessionId);
        $this->em->persist($cart);
        $this->em->flush();

        return $cart;
    }

    /**
     * Get cart for an authenticated user.
     */
    public function getCartForUser(User $user): Cart
    {
        $cart = $this->em->getRepository(Cart::class)->findOneBy([
            'user'   => $user,
            'status' => 'active',
        ]);

        if ($cart instanceof Cart) {
            return $cart;
        }

        $cart = new Cart($user, null);
        $this->em->persist($cart);
        $this->em->flush();

        return $cart;
    }

    /**
     * Merge guest cart into user cart (called on login).
     */
    public function mergeSessionCartIntoUser(string $sessionId, User $user): void
    {
        if ($sessionId === '') {
            return;
        }

        $guestCart = $this->em->getRepository(Cart::class)->findOneBy([
            'sessionId' => $sessionId,
            'status'    => 'active',
        ]);

        if (! $guestCart) {
            return;
        }

        $userCart = $this->getCartForUser($user);

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

        $guestCart->setStatus('abandoned');

        $this->em->flush();
    }

    /**
     * Migrate session cart if session ID changes.
     */
    public function migrateCart(string $oldSessionId, string $newSessionId): void
    {
        if ($oldSessionId === '' || $newSessionId === '') {
            throw new InvalidArgumentException('Both session IDs are required.');
        }

        if ($oldSessionId === $newSessionId) {
            return;
        }

        $cart = $this->em->getRepository(Cart::class)->findOneBy([
            'sessionId' => $oldSessionId,
            'status'    => 'active',
        ]);

        if (! $cart) {
            return;
        }

        $cart->setSessionId($newSessionId);
        $this->em->flush();
    }

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
        $this->em->flush();

        return $item;
    }

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
        $this->em->flush();

        return $item;
    }

    public function removeItem(Cart $cart, UuidInterface $itemId): void
    {
        $cart->removeItemById($itemId);
        $this->em->flush();
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        $item->setQuantity($quantity);
        $item->getCart()->recalculate();
        $this->em->flush();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->clear();
        $this->em->flush();
    }

    public function applyCoupon(Cart $cart, string $code): bool
    {
        $discount = (int) ($cart->getSubtotal() * 0.10);

        $cart->applyCoupon($code, $discount);
        $this->em->flush();

        return true;
    }

    public function removeCoupon(Cart $cart): void
    {
        $cart->removeCoupon();
        $this->em->flush();
    }

    public function completeCart(Cart $cart): void
    {
        $cart->complete();
        $this->em->flush();
    }
}
