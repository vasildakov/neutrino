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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function sprintf;

#[ORM\Entity]
#[ORM\Table(name: 'subscriptions')]
#[ORM\UniqueConstraint(name: 'uniq_subscription_account', columns: ['account_id'])]
#[ORM\Index(name: 'idx_subscription_status', columns: ['status'])]
#[ORM\Index(name: 'idx_subscription_provider', columns: ['provider', 'provider_subscription_id'])]
class Subscription
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    #[ORM\OneToOne(targetEntity: Account::class, inversedBy: 'subscription')]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    // e.g. stripe / paddle / manual
    #[ORM\Column(type: 'string', length: 30)]
    private string $provider;

    // e.g. Stripe subscription id "sub_..."
    #[ORM\Column(name: 'provider_subscription_id', type: 'string', length: 120, nullable: true)]
    private ?string $providerSubscriptionId = null;

    #[ORM\Column(type: 'string', length: 120)]
    private string $status;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $plan = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $amount = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $currency = null;

    #[ORM\Column(name: 'current_period_start', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $currentPeriodStart = null;

    #[ORM\Column(name: 'current_period_end', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $currentPeriodEnd = null;

    /**
     * Payment gateway customer ID (e.g., Stripe customer ID, PayPal customer ID)
     * Safe to store - this is a token, not card data
     */
    #[ORM\Column(name: 'payment_customer_id', type: 'string', length: 255, nullable: true)]
    private ?string $paymentCustomerId = null;

    /**
     * Payment gateway payment method ID (e.g., Stripe payment method ID)
     * Safe to store - this is a token reference, not actual card data
     */
    #[ORM\Column(name: 'payment_method_id', type: 'string', length: 255, nullable: true)]
    private ?string $paymentMethodId = null;

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

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id        = Uuid::uuid4();
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): UuidInterface|string
    {
        return $this->id;
    }

    public function account(): Account
    {
        return $this->account;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function providerSubscriptionId(): ?string
    {
        return $this->providerSubscriptionId;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function plan(): ?string
    {
        return $this->plan;
    }

    public function amount(): ?string
    {
        return $this->amount;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function currentPeriodStart(): ?DateTimeImmutable
    {
        return $this->currentPeriodStart;
    }

    public function currentPeriodEnd(): ?DateTimeImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get payment gateway customer ID (safe - it's a token, not card data)
     */
    public function paymentCustomerId(): ?string
    {
        return $this->paymentCustomerId;
    }

    /**
     * Set payment gateway customer ID (e.g., Stripe customer ID)
     */
    public function setPaymentCustomerId(?string $customerId): void
    {
        $this->paymentCustomerId = $customerId;
        $this->updatedAt         = new DateTimeImmutable();
    }

    /**
     * Get payment method ID (safe - it's a token reference)
     */
    public function paymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    /**
     * Set payment method ID with card metadata
     *
     * @param string|null $paymentMethodId Gateway payment method token
     * @param string|null $last4 Last 4 digits (safe to store per PCI DSS)
     * @param string|null $brand Card brand (Visa, Mastercard, etc.)
     */
    public function setPaymentMethod(
        ?string $paymentMethodId,
        ?string $last4 = null,
        ?string $brand = null
    ): void {
        $this->paymentMethodId = $paymentMethodId;
        $this->cardLast4       = $last4;
        $this->cardBrand       = $brand;
        $this->updatedAt       = new DateTimeImmutable();
    }

    /**
     * Get the last 4 digits of the card (safe to display)
     */
    public function cardLast4(): ?string
    {
        return $this->cardLast4;
    }

    /**
     * Get card brand
     */
    public function cardBrand(): ?string
    {
        return $this->cardBrand;
    }

    /**
     * Get formatted card display (e.g., "Visa •••• 4242")
     */
    public function getCardDisplay(): ?string
    {
        if (! $this->cardBrand || ! $this->cardLast4) {
            return null;
        }

        return sprintf('%s •••• %s', $this->cardBrand, $this->cardLast4);
    }
}
