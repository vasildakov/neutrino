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
 * Payment record
 *
 * SECURITY: Only stores payment gateway tokens/references, NOT card data
 */
#[ORM\Entity]
#[ORM\Table(name: 'billing_payments')]
#[ORM\Index(name: 'idx_payment_account', columns: ['account_id'])]
#[ORM\Index(name: 'idx_payment_invoice', columns: ['invoice_id'])]
#[ORM\Index(name: 'idx_payment_status', columns: ['status'])]
#[ORM\Index(name: 'idx_payment_transaction', columns: ['transaction_id'])]
class BillingPayment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ORM\ManyToOne(targetEntity: BillingInvoice::class)]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?BillingInvoice $invoice = null;

    #[ORM\ManyToOne(targetEntity: Subscription::class)]
    #[ORM\JoinColumn(name: 'subscription_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Subscription $subscription = null;

    /**
     * Payment gateway transaction ID (safe to store - it's a reference token)
     */
    #[ORM\Column(name: 'transaction_id', type: 'string', length: 255, unique: true)]
    private string $transactionId;

    /**
     * Payment provider (paypal, stripe, fake, etc.)
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $provider;

    /**
     * Payment status: pending, completed, failed, refunded, cancelled
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'pending';

    /**
     * Amount in cents
     */
    #[ORM\Column(type: 'integer')]
    private int $amount;

    /**
     * Currency code (ISO 4217)
     */
    #[ORM\Column(type: 'string', length: 3)]
    private string $currency = 'USD';

    /**
     * Payment method type (card, paypal, bank_transfer, etc.)
     */
    #[ORM\Column(name: 'payment_method', type: 'string', length: 50, nullable: true)]
    private ?string $paymentMethod = null;

    /**
     * Last 4 digits of card (safe to store per PCI DSS)
     */
    #[ORM\Column(name: 'card_last4', type: 'string', length: 4, nullable: true)]
    private ?string $cardLast4 = null;

    /**
     * Card brand (Visa, Mastercard, etc.) - safe to store
     */
    #[ORM\Column(name: 'card_brand', type: 'string', length: 50, nullable: true)]
    private ?string $cardBrand = null;

    /**
     * Description/memo
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * Failure reason (if payment failed)
     */
    #[ORM\Column(name: 'failure_reason', type: 'text', nullable: true)]
    private ?string $failureReason = null;

    /**
     * Payment date (when completed)
     */
    #[ORM\Column(name: 'payment_date', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $paymentDate = null;

    /**
     * Refund date (if refunded)
     */
    #[ORM\Column(name: 'refund_date', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $refundDate = null;

    /**
     * Raw gateway response (for debugging)
     */
    #[ORM\Column(name: 'gateway_response', type: 'json', nullable: true)]
    private ?array $gatewayResponse = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        Account $account,
        string $transactionId,
        string $provider,
        int $amount,
        string $currency = 'USD',
        ?string $description = null
    ) {
        $this->account = $account;
        $this->transactionId = $transactionId;
        $this->provider = $provider;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->description = $description;
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

    public function getInvoice(): ?BillingInvoice
    {
        return $this->invoice;
    }

    public function setInvoice(?BillingInvoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): void
    {
        $this->subscription = $subscription;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getStatus(): string
    {
        return $this->status;
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

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(
        ?string $method,
        ?string $last4 = null,
        ?string $brand = null
    ): void {
        $this->paymentMethod = $method;
        $this->cardLast4 = $last4;
        $this->cardBrand = $brand;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCardLast4(): ?string
    {
        return $this->cardLast4;
    }

    public function getCardBrand(): ?string
    {
        return $this->cardBrand;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function getPaymentDate(): ?DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function getRefundDate(): ?DateTimeImmutable
    {
        return $this->refundDate;
    }

    public function getGatewayResponse(): ?array
    {
        return $this->gatewayResponse;
    }

    public function setGatewayResponse(?array $response): void
    {
        $this->gatewayResponse = $response;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->paymentDate = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(?string $reason = null): void
    {
        $this->status = 'failed';
        $this->failureReason = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Mark payment as refunded
     */
    public function markAsRefunded(): void
    {
        $this->status = 'refunded';
        $this->refundDate = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Cancel payment
     */
    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
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
     * Get formatted display (e.g., "FAKE-TXN-123 - $99.99 - completed")
     */
    public function getDisplay(): string
    {
        return sprintf(
            '%s - %s %.2f - %s',
            $this->transactionId,
            $this->currency,
            $this->getAmountFormatted(),
            $this->status
        );
    }

    /**
     * Get card display if available
     */
    public function getCardDisplay(): ?string
    {
        if (!$this->cardBrand || !$this->cardLast4) {
            return null;
        }

        return sprintf('%s •••• %s', $this->cardBrand, $this->cardLast4);
    }
}

