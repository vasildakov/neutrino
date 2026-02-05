<?php

declare(strict_types=1);

namespace Neutrino\Domain\Store;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'store_databases')]
#[ORM\UniqueConstraint(name: 'uniq_store_database_name', columns: ['db_name'])]
class StoreDatabase
{
    public function __construct(
        #[ORM\Id]
        #[ORM\OneToOne(targetEntity: Store::class, inversedBy: 'database')]
        #[ORM\JoinColumn(name: 'store_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private Store $store,

        #[ORM\Column(name: 'db_name', type: 'string', length: 128)]
        private string $dbName,

        #[ORM\Column(name: 'db_user', type: 'string', length: 128)]
        private string $dbUser,

        #[ORM\Column(name: 'db_password', type: 'string', length: 255, nullable: true)]
        private ?string $dbPassword,
    ) {}

    public static function forStore(Store $store, string $dbName, string $dbUser, ?string $dbPassword): self
    {
        return new self($store, $dbName, $dbUser, $dbPassword);
    }

    public function storeId(): StoreId
    {
        return $this->store->id();
    }

    public function dbName(): string
    {
        return $this->dbName;
    }
}
