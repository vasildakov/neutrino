<?php

declare(strict_types=1);

namespace Neutrino\Entity;

#[ORM\Entity]
#[ORM\Table(name: 'stores')]
#[ORM\Index(name: 'idx_store_account', columns: ['account_id'])]
class Store
{
    #[ORM\OneToOne(mappedBy: 'store', targetEntity: StoreDatabase::class, cascade: ['persist'], orphanRemoval: true)]
    private ?StoreDatabase $database = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'store_id', length: 36)]
        private StoreId $id,

        #[ORM\ManyToOne(targetEntity: Account::class)]
        #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private Account $account,

        #[ORM\Column(type: 'string', length: 120)]
        private string $name,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(Account $account, string $name): self
    {
        return new self(StoreId::new(), $account, $name);
    }

    public function id(): StoreId
    {
        return $this->id;
    }

    public function accountId(): AccountId
    {
        return $this->account->id();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function database(): ?StoreDatabase
    {
        return $this->database;
    }

    public function attachDatabase(StoreDatabase $database): void
    {
        if ($database->storeId()->value !== $this->id->value) {
            throw new \DomainException('Database must belong to this store.');
        }

        $this->database = $database;
    }
}
