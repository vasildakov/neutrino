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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Neutrino\Domain\Account\Account;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

use function sprintf;

/**
 * Invoice for billing purposes
 */
#[ORM\Entity]
#[ORM\Table(name: 'billing_invoices')]
#[ORM\Index(name: 'idx_invoice_account', columns: ['account_id'])]
#[ORM\Index(name: 'idx_invoice_status', columns: ['status'])]
#[ORM\Index(name: 'idx_invoice_number', columns: ['invoice_number'])]
#[ORM\Index(name: 'idx_invoice_date', columns: ['invoice_date'])]
class BillingInvoice
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ORM\OneToOne(targetEntity: Subscription::class)]
    #[ORM\JoinColumn(name: 'subscription_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Subscription $subscription = null;

    /**
     * Invoice number (e.g., INV-2026-001)
     */
    #[ORM\Column(name: 'invoice_number', type: 'string', length: 50, unique: true)]
    private string $invoiceNumber;

    /**
     * Invoice status: draft, issued, paid, cancelled, overdue
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'draft';

    /**
     * Invoice date
     */
    #[ORM\Column(name: 'invoice_date', type: 'datetime_immutable')]
    private DateTimeImmutable $invoiceDate;

    /**
     * Due date
     */
    #[ORM\Column(name: 'due_date', type: 'datetime_immutable')]
    private DateTimeImmutable $dueDate;

    /**
     * Payment date (when actually paid)
     */
    #[ORM\Column(name: 'payment_date', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $paymentDate = null;

    /**
     * Subtotal before tax (in cents)
     */
    #[ORM\Column(type: 'integer')]
    private int $subtotal = 0;

    /**
     * Tax amount (in cents)
     */
    #[ORM\Column(name: 'tax_amount', type: 'integer')]
    private int $taxAmount = 0;

    /**
     * Tax rate (percentage)
     */
    #[ORM\Column(name: 'tax_rate', type: 'decimal', precision: 5, scale: 2)]
    private string $taxRate = '0.00';

    /**
     * Total amount (subtotal + tax) in cents
     */
    #[ORM\Column(type: 'integer')]
    private int $total = 0;

    /**
     * Currency code (ISO 4217)
     */
    #[ORM\Column(type: 'string', length: 3)]
    private string $currency = 'USD';

    /**
     * Invoice lines (items)
     *
     * @var Collection<int, BillingInvoiceLine>
     */
    #[ORM\OneToMany(targetEntity: BillingInvoiceLine::class, mappedBy: 'invoice', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lines;

    /**
     * Notes/memo for the invoice
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /**
     * Payment provider transaction ID
     */
    #[ORM\Column(name: 'transaction_id', type: 'string', length: 255, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        Account $account,
        string $invoiceNumber,
        DateTimeImmutable $invoiceDate,
        DateTimeImmutable $dueDate,
        string $currency = 'USD'
    ) {
        $this->account = $account;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate = $invoiceDate;
        $this->dueDate = $dueDate;
        $this->currency = $currency;
        $this->lines = new ArrayCollection();
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

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): void
    {
        $this->subscription = $subscription;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getInvoiceDate(): DateTimeImmutable
    {
        return $this->invoiceDate;
    }

    public function getDueDate(): DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getPaymentDate(): ?DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function markAsPaid(?string $transactionId = null): void
    {
        $this->status = 'paid';
        $this->paymentDate = new DateTimeImmutable();
        $this->transactionId = $transactionId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsOverdue(): void
    {
        $this->status = 'overdue';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function issue(): void
    {
        $this->status = 'issued';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Add invoice line
     */
    public function addLine(BillingInvoiceLine $line): void
    {
        if (!$this->lines->contains($line)) {
            $this->lines->add($line);
            $this->recalculate();
        }
    }

    /**
     * Remove invoice line
     */
    public function removeLine(BillingInvoiceLine $line): void
    {
        if ($this->lines->contains($line)) {
            $this->lines->removeElement($line);
            $this->recalculate();
        }
    }

    /**
     * Get all invoice lines
     *
     * @return Collection<int, BillingInvoiceLine>
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    /**
     * Recalculate totals based on invoice lines
     */
    public function recalculate(): void
    {
        $this->subtotal = 0;

        foreach ($this->lines as $line) {
            $this->subtotal += $line->getTotal();
        }

        // Calculate tax
        $taxRate = (float) $this->taxRate;
        $this->taxAmount = (int) round($this->subtotal * ($taxRate / 100));

        // Calculate total
        $this->total = $this->subtotal + $this->taxAmount;

        $this->updatedAt = new DateTimeImmutable();
    }

    public function setTaxRate(string $taxRate): void
    {
        $this->taxRate = $taxRate;
        $this->recalculate();
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

    public function getTaxRate(): string
    {
        return $this->taxRate;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'paid' || $this->status === 'cancelled') {
            return false;
        }

        return new DateTimeImmutable() > $this->dueDate;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
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
     * Get formatted display (e.g., "INV-2026-001 - $99.99")
     */
    public function getDisplay(): string
    {
        return sprintf(
            '%s - %s %.2f',
            $this->invoiceNumber,
            $this->currency,
            $this->getTotalFormatted()
        );
    }
}
