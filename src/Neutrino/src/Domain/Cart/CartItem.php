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
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

use function max;
use function ucfirst;

/**
 * Cart Item - represents an item in the shopping cart
 */
#[ORM\Entity]
#[ORM\Table(name: 'cart_items')]
#[ORM\Index(name: 'idx_cart_item_cart', columns: ['cart_id'])]
#[ORM\Index(name: 'idx_cart_item_type', columns: ['item_type'])]
class CartItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Cart $cart;

    /**
     * Item type: plan, addon, extra, etc.
     */
    #[ORM\Column(name: 'item_type', type: 'string', length: 50)]
    private string $itemType;

    /**
     * Item ID (e.g., plan UUID, addon UUID)
     */
    #[ORM\Column(name: 'item_id', type: 'uuid')]
    private UuidInterface $itemId;

    /**
     * Item name/description
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    /**
     * Billing period: monthly, yearly (for plans/subscriptions)
     */
    #[ORM\Column(name: 'billing_period', type: 'string', length: 20, nullable: true)]
    private ?string $billingPeriod = null;

    /**
     * Quantity
     */
    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    /**
     * Unit price in cents
     */
    #[ORM\Column(name: 'unit_price', type: 'integer')]
    private int $unitPrice;

    /**
     * Total price (quantity * unitPrice) in cents
     */
    #[ORM\Column(type: 'integer')]
    private int $total;

    /**
     * Additional metadata (JSON)
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        Cart $cart,
        string $itemType,
        UuidInterface $itemId,
        string $name,
        int $unitPrice,
        int $quantity = 1,
        ?string $billingPeriod = null,
        ?array $metadata = null
    ) {
        $this->cart          = $cart;
        $this->itemType      = $itemType;
        $this->itemId        = $itemId;
        $this->name          = $name;
        $this->unitPrice     = $unitPrice;
        $this->quantity      = $quantity;
        $this->billingPeriod = $billingPeriod;
        $this->metadata      = $metadata;
        $this->total         = $unitPrice * $quantity;
        $this->createdAt     = new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function getItemId(): UuidInterface
    {
        return $this->itemId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBillingPeriod(): ?string
    {
        return $this->billingPeriod;
    }

    public function setBillingPeriod(?string $period): void
    {
        $this->billingPeriod = $period;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = max(1, $quantity);
        $this->recalculate();
    }

    public function incrementQuantity(int $amount = 1): void
    {
        $this->quantity += $amount;
        $this->recalculate();
    }

    public function decrementQuantity(int $amount = 1): void
    {
        $this->quantity = max(1, $this->quantity - $amount);
        $this->recalculate();
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getUnitPriceFormatted(): float
    {
        return $this->unitPrice / 100;
    }

    public function setUnitPrice(int $price): void
    {
        $this->unitPrice = $price;
        $this->recalculate();
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTotalFormatted(): float
    {
        return $this->total / 100;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function setMetadataValue(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Recalculate total
     */
    private function recalculate(): void
    {
        $this->total = $this->unitPrice * $this->quantity;
    }

    /**
     * Check if this is a plan item
     */
    public function isPlan(): bool
    {
        return $this->itemType === 'plan';
    }

    /**
     * Check if this is an addon item
     */
    public function isAddon(): bool
    {
        return $this->itemType === 'addon';
    }

    /**
     * Get display name with billing period
     */
    public function getDisplayName(): string
    {
        $name = $this->name;

        if ($this->billingPeriod) {
            $name .= ' - ' . ucfirst($this->billingPeriod);
        }

        return $name;
    }
}
