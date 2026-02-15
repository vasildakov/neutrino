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
namespace Neutrino\Domain\Store;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Neutrino\Domain\Account\Account;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'stores')]
#[ORM\Index(name: 'idx_store_account', columns: ['account_id'])]
class Store
{
    private const BASE_DOMAIN = 'neutrino.bg';

    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    #[ORM\Embedded(class: StoreSlug::class)]
    private StoreSlug $slug;

    #[ORM\OneToOne(targetEntity: StoreDatabase::class, mappedBy: 'store', cascade: ['persist'], orphanRemoval: true)]
    private ?StoreDatabase $database = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'stores')]
        #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false)]
        private Account $account,

        #[ORM\Column(type: 'string', length: 120)]
        private string $name,

        StoreSlug $slug,
    ) {
        $this->slug = $slug;
        $this->createdAt = new DateTimeImmutable();
    }


    public function account(): Account
    {
        return $this->account;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): StoreSlug
    {
        return $this->slug;
    }

    public function domain(): string
    {
        return $this->slug->getValue() . '.' . self::BASE_DOMAIN;
    }

    public function database(): ?StoreDatabase
    {
        return $this->database;
    }

    public function attachDatabase(StoreDatabase $database): void
    {
        $this->database = $database;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'domain' => $this->domain(),
        ];
    }
}