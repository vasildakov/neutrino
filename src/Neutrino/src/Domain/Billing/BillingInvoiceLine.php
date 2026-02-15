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
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * Invoice line item (individual charge on an invoice)
 */
#[ORM\Entity]
#[ORM\Table(name: 'billing_invoice_lines')]
#[ORM\Index(name: 'idx_invoice_line_invoice', columns: ['invoice_id'])]
class BillingInvoiceLine
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: BillingInvoice::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private BillingInvoice $invoice;

    /**
     * Line description (e.g., "Pro Plan - Monthly", "Additional Storage")
     */
    #[ORM\Column(type: 'string', length: 255)]
    private string $description;

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
     * Period start (for subscription-based items)
     */
    #[ORM\Column(name: 'period_start', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $periodStart = null;

    /**
     * Period end (for subscription-based items)
     */
    #[ORM\Column(name: 'period_end', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $periodEnd = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        BillingInvoice $invoice,
        string $description,
        int $unitPrice,
        int $quantity = 1,
        ?DateTimeImmutable $periodStart = null,
        ?DateTimeImmutable $periodEnd = null
    ) {
        $this->invoice     = $invoice;
        $this->description = $description;
        $this->unitPrice   = $unitPrice;
        $this->quantity    = $quantity;
        $this->periodStart = $periodStart;
        $this->periodEnd   = $periodEnd;
        $this->total       = $unitPrice * $quantity;
        $this->createdAt   = new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getInvoice(): BillingInvoice
    {
        return $this->invoice;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
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

    public function setUnitPrice(int $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
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

    public function getPeriodStart(): ?DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function getPeriodEnd(): ?DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function setPeriod(?DateTimeImmutable $start, ?DateTimeImmutable $end): void
    {
        $this->periodStart = $start;
        $this->periodEnd   = $end;
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
}
