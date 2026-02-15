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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'plans')]
#[ORM\UniqueConstraint(name: 'uniq_plan_key', columns: ['plan_key'])]
#[ORM\Index(name: 'idx_plan_active', columns: ['is_active'])]
class Plan
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    // Internal stable identifier: free, pro, business
    #[ORM\Column(name: 'plan_key', type: Types::STRING, length: 50)]
    private string $key;

    #[ORM\Column(type: Types::STRING, length: 150)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    // price in cents (avoid float)
    #[ORM\Column(type: Types::INTEGER)]
    private int $priceAmount;

    #[ORM\Column(name: 'max_stores', type: Types::INTEGER)]
    private int $maxStores;

    #[ORM\Column(name: 'max_users', type: Types::INTEGER)]
    private int $maxUsers;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $features = null;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
    private bool $isActive = true;

    public function __construct(
        string $key,
        string $name,
        int $priceAmount,
        int $maxStores,
        int $maxUsers,
        ?string $description = null,
        ?string $icon = null,
        ?array $features = null
    ) {
        $this->id          = Uuid::uuid4();
        $this->key         = $key;
        $this->name        = $name;
        $this->priceAmount = $priceAmount;
        $this->maxStores   = $maxStores;
        $this->maxUsers    = $maxUsers;
        $this->description = $description;
        $this->icon        = $icon;
        $this->features    = $features;
    }

    public function getId(): UuidInterface|string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPriceAmount(): int
    {
        return $this->priceAmount;
    }

    public function getPriceMonthly(): float
    {
        return $this->priceAmount / 100;
    }

    public function getPriceYearly(): float
    {
        // Assuming 30% discount for yearly (save 30%)
        return ($this->priceAmount * 12 * 0.70) / 100;
    }

    public function getMaxStores(): int
    {
        return $this->maxStores;
    }

    public function getMaxUsers(): int
    {
        return $this->maxUsers;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getFeatures(): ?array
    {
        return $this->features;
    }
}
