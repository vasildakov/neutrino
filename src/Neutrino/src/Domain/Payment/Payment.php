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
namespace Neutrino\Domain\Payment;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Neutrino\Domain\Account\Account;
use Neutrino\Domain\Order\Order;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
#[ORM\Index(name: 'idx_payments_account', columns: ['account_id'])]
#[ORM\Index(name: 'idx_payments_order', columns: ['order_id'])]
#[ORM\Index(name: 'idx_payments_provider_ref', columns: ['provider', 'provider_ref'])]
#[ORM\Index(name: 'idx_payments_status', columns: ['status'])]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false)]
    private Account $account;

    #[ORM\ManyToOne(targetEntity: Order::class)]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: true)]
    private ?Order $order = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $provider;

    #[ORM\Column(name: 'provider_ref', type: "string", length: 255, nullable: true)]
    private ?string $providerRef = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'string', length: 30)]
    private string $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
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

    public function order(): ?Order
    {
        return $this->order;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function providerRef(): ?string
    {
        return $this->providerRef;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
