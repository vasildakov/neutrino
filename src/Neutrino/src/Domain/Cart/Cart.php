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

namespace Neutrino\Domain\Cart;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Neutrino\Domain\User\User;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

use function max;
use function round;

/**
 * Shopping Cart
 */
#[ORM\Entity]
#[ORM\Table(name: 'carts')]
#[ORM\Index(name: 'idx_cart_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_cart_session', columns: ['session_id'])]
#[ORM\Index(name: 'idx_cart_status', columns: ['status'])]
class Cart
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    /**
     * User (for logged-in users)
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * Session ID (for guest users)
     */
    #[ORM\Column(name: 'session_id', type: 'string', length: 255, nullable: true)]
    private ?string $sessionId = null;

    /**
     * Cart status: active, completed, abandoned, merged
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'active';

    /**
     * Cart items
     *
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(
        targetEntity: CartItem::class,
        mappedBy: 'cart',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $items;

    /**
     * Subtotal in cents
     */
    #[ORM\Column(type: 'integer')]
    private int $subtotal = 0;

    /**
     * Tax amount in cents
     */
    #[ORM\Column(name: 'tax_amount', type: 'integer')]
    private int $taxAmount = 0;

    /**
     * Total amount in cents
     */
    #[ORM\Column(type: 'integer')]
    private int $total = 0;

    /**
     * Currency code
     */
    #[ORM\Column(type: 'string', length: 3)]
    private string $currency = 'USD';

    /**
     * Coupon code applied
     */
    #[ORM\Column(name: 'coupon_code', type: 'string', length: 50, nullable: true)]
    private ?string $couponCode = null;

    /**
     * Discount amount in cents
     */
    #[ORM\Column(name: 'discount_amount', type: 'integer')]
    private int $discountAmount = 0;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'completed_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $completedAt = null;

    public function __construct(?User $user = null, ?string $sessionId = null)
    {
        $this->user      = $user;
        $this->sessionId = $sessionId;
        $this->items     = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user      = $user;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get all items in cart
     *
     * @return Collection<int, CartItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Add item to cart
     */
    public function addItem(CartItem $item): void
    {
        // For plans: only one plan allowed - replace existing plan
        if ($item->getItemType() === 'plan') {
            $existingPlan = $this->getPlanItem();
            if ($existingPlan) {
                $this->removeItem($existingPlan);
            }
            $this->items->add($item);
        } else {
            // For other items: check if the item already exists
            $existing = $this->findItemByTypeAndId($item->getItemType(), $item->getItemId());

            if ($existing) {
                // Update quantity
                $existing->setQuantity($existing->getQuantity() + $item->getQuantity());
            } else {
                $this->items->add($item);
            }
        }

        $this->recalculate();
    }

    /**
     * Remove item from the cart
     */
    public function removeItem(CartItem $item): void
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            $this->recalculate();
        }
    }

    /**
     * Remove item by ID
     */
    public function removeItemById(UuidInterface $itemId): void
    {
        foreach ($this->items as $item) {
            if ($item->getId()->equals($itemId)) {
                $this->removeItem($item);
                break;
            }
        }
    }

    /**
     * Find item by type and item ID
     */
    private function findItemByTypeAndId(string $type, UuidInterface $itemId): ?CartItem
    {
        foreach ($this->items as $item) {
            if ($item->getItemType() === $type && $item->getItemId()->equals($itemId)) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Clear all items
     */
    public function clear(): void
    {
        $this->items->clear();
        $this->recalculate();
    }

    /**
     * Get item count
     */
    public function getItemCount(): int
    {
        return $this->items->count();
    }

    /**
     * Get total quantity
     */
    public function getTotalQuantity(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getQuantity();
        }
        return $total;
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Apply coupon
     */
    public function applyCoupon(string $code, int $discountAmount): void
    {
        $this->couponCode     = $code;
        $this->discountAmount = $discountAmount;
        $this->recalculate();
    }

    /**
     * Remove coupon
     */
    public function removeCoupon(): void
    {
        $this->couponCode     = null;
        $this->discountAmount = 0;
        $this->recalculate();
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function getDiscountAmount(): int
    {
        return $this->discountAmount;
    }

    public function getDiscountAmountFormatted(): float
    {
        return $this->discountAmount / 100;
    }

    /**
     * Recalculate totals
     */
    public function recalculate(): void
    {
        $this->subtotal = 0;

        foreach ($this->items as $item) {
            $this->subtotal += $item->getTotal();
        }

        // Apply discount
        $subtotalAfterDiscount = max(0, $this->subtotal - $this->discountAmount);

        // Calculate tax (20%)
        $this->taxAmount = (int) round($subtotalAfterDiscount * 0.20);

        // Calculate total
        $this->total = $subtotalAfterDiscount + $this->taxAmount;

        $this->updatedAt = new DateTimeImmutable();
    }

    public function getSubtotal(): int
    {
        return $this->subtotal;
    }

    public function getSubtotalFormatted(): float
    {
        return $this->subtotal / 100;
    }

    public function getTaxAmount(): int
    {
        return $this->taxAmount;
    }

    public function getTaxAmountFormatted(): float
    {
        return $this->taxAmount / 100;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTotalFormatted(): float
    {
        return $this->total / 100;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Mark cart as completed
     */
    public function complete(): void
    {
        $this->status      = 'completed';
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt   = new DateTimeImmutable();
    }

    /**
     * Mark cart as abandoned
     */
    public function abandon(): void
    {
        $this->status    = 'abandoned';
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Check if the cart is expired (30 days old)
     */
    public function isExpired(): bool
    {
        $expiryDate = $this->createdAt->modify('+30 days');
        return new DateTimeImmutable() > $expiryDate;
    }

    /**
     * Check if the cart has a plan item
     */
    public function hasPlan(): bool
    {
        return $this->getPlanItem() !== null;
    }

    /**
     * Get the plan item from the cart
     */
    public function getPlanItem(): ?CartItem
    {
        foreach ($this->items as $item) {
            if ($item->getItemType() === 'plan') {
                return $item;
            }
        }
        return null;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }
}
