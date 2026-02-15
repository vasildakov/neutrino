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

namespace Neutrino\Domain\Billing;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Neutrino\Domain\Account\Account;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

use function sprintf;

/**
 * Order entity - represents a customer order before payment
 */
#[ORM\Entity]
#[ORM\Table(name: 'billing_orders')]
#[ORM\Index(name: 'idx_order_account', columns: ['account_id'])]
#[ORM\Index(name: 'idx_order_status', columns: ['status'])]
#[ORM\Index(name: 'idx_order_number', columns: ['order_number'])]
class BillingOrder
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ORM\ManyToOne(targetEntity: Plan::class)]
    #[ORM\JoinColumn(name: 'plan_id', referencedColumnName: 'id', nullable: false)]
    private Plan $plan;

    /**
     * Order number (e.g., ORD-2026-001)
     */
    #[ORM\Column(name: 'order_number', type: 'string', length: 50, unique: true)]
    private string $orderNumber;

    /**
     * Order status: pending, processing, completed, cancelled, failed
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'pending';

    /**
     * Billing period: monthly, yearly
     */
    #[ORM\Column(name: 'billing_period', type: 'string', length: 20)]
    private string $billingPeriod;

    /**
     * Order amount in cents
     */
    #[ORM\Column(type: 'integer')]
    private int $amount;

    /**
     * Currency code (ISO 4217)
     */
    #[ORM\Column(type: 'string', length: 3)]
    private string $currency = 'USD';

    /**
     * Customer email
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    /**
     * Billing address
     */
    #[ORM\Column(name: 'billing_address', type: 'json', nullable: true)]
    private ?array $billingAddress = null;

    /**
     * Order notes
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /**
     * Payment reference (transaction ID when paid)
     */
    #[ORM\Column(name: 'payment_reference', type: 'string', length: 255, nullable: true)]
    private ?string $paymentReference = null;

    /**
     * Date when order was completed
     */
    #[ORM\Column(name: 'completed_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $completedAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        Account $account,
        Plan $plan,
        string $orderNumber,
        string $billingPeriod,
        int $amount,
        string $email,
        string $currency = 'USD'
    ) {
        $this->account = $account;
        $this->plan = $plan;
        $this->orderNumber = $orderNumber;
        $this->billingPeriod = $billingPeriod;
        $this->amount = $amount;
        $this->email = $email;
        $this->currency = $currency;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getPlan(): Plan
    {
        return $this->plan;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getBillingPeriod(): string
    {
        return $this->billingPeriod;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getAmountFormatted(): float
    {
        return $this->amount / 100;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getBillingAddress(): ?array
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?array $address): void
    {
        $this->billingAddress = $address;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    /**
     * Mark order as processing
     */
    public function markAsProcessing(): void
    {
        $this->status = 'processing';
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Mark order as completed
     */
    public function markAsCompleted(?string $paymentReference = null): void
    {
        $this->status = 'completed';
        $this->paymentReference = $paymentReference;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Mark order as failed
     */
    public function markAsFailed(?string $reason = null): void
    {
        $this->status = 'failed';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Cancel order
     */
    public function cancel(?string $reason = null): void
    {
        $this->status = 'cancelled';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Get formatted display (e.g., "ORD-2026-001 - Pro Plan - $49.00")
     */
    public function getDisplay(): string
    {
        return sprintf(
            '%s - %s - %s %.2f',
            $this->orderNumber,
            $this->plan->getName(),
            $this->currency,
            $this->getAmountFormatted()
        );
    }
}

